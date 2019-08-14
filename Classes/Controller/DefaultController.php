<?php
namespace YolfTypo3\SavLibraryMvc\Controller;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with TYPO3 source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use YolfTypo3\SavLibraryMvc\Persistence\ObjectStorage;

/**
 * Default controller for the SAV Library MVC
 */
class DefaultController extends AbstractController
{

    /**
     * List action
     *
     * @param string $special
     * @return void
     */
    public function listAction($special = null)
    {
        $arguments = [
            'special' => $special
        ];
        $viewConfiguration = $this->getViewConfiguration($arguments);

        // Sets the view parameters
        $this->view->assign('general', $viewConfiguration['general']);
        $this->view->assign('items', $viewConfiguration['items']);
    }

    /**
     * Single action
     *
     * @param string $special
     * @return void
     */
    public function singleAction($special = null)
    {
        $arguments = [
            'special' => $special
        ];
        $viewConfiguration = $this->getViewConfiguration($arguments);

        // Sets the view parameters
        $this->view->assign('general', $viewConfiguration['general']);
        $this->view->assign('fields', $viewConfiguration['fields']);
        $this->view->assign('folders', $viewConfiguration['folders']);
    }

    /**
     * Edit action
     *
     * @param string $special
     * @return void
     */
    public function editAction($special = null)
    {
        // Checks if the user is authenticated
        if ($this->getFrontendUserManager()->userIsAuthenticated() === false) {
            FlashMessages::addError('fatal.notAuthenticated');
            return $this->redirect('single', null, null, [
                'special' => $special
            ]);
        }

        $arguments = [
            'special' => $special
        ];
        $viewConfiguration = $this->getViewConfiguration($arguments);

        // Sets the view parameters
        $this->view->assign('general', $viewConfiguration['general']);
        $this->view->assign('fields', $viewConfiguration['fields']);
        $this->view->assign('folders', $viewConfiguration['folders']);
    }

    /**
     * Save method called by saveAction in the controller class
     *
     * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $data
     * @return void
     */
    public function save($data)
    {
        // Gets the arguments
        $arguments = $this->request->getArguments();

        // Sets special
        $special = $arguments['special'];
        $uncompressedParameters = self::uncompressParameters($special);

        // Checks if the user is authenticated
        if ($this->getFrontendUserManager()->userIsAuthenticated() === false) {
            FlashMessages::addError('fatal.notAuthenticated');
            return $this->redirect('single', null, null, [
                'special' => $special
            ]);
        }

        // Gets the arguments
        $arguments = $this->request->getArguments();
        $isNewItemInSubform = isset($uncompressedParameters['subformUidForeign']) && $uncompressedParameters['subformUidForeign'] == - 1;

        // Updates the data
        if (is_null($data->getUid())) {
            // New record in the main form. Creates a new object in the main repository and gets its uid

            // Sets the cruser_id_frontend field
            // Currently Extbase sets cruser_id to 0 when data are input in front end.
            // The field cruser_id_frontend is in the default model and is created for all generated extensions.
            // It was introduced to recover the id of the frontend user who created the record.
            $data->setCruserIdFrontend($GLOBALS['TSFE']->fe_user->user['uid']);

            $this->getMainRepository()->add($data);
            $this->getMainRepository()->persistAll();
            $object = $this->getMainRepository()->findByIdentifier($data);
            $uid = $object->getUid();
        } elseif ($isNewItemInSubform) {
            // New record in subform.
            // The choice was to create the new object when saving and not when clicking on
            // the new icon. y doing so, the record is created only if saved.
            // A path is sent to retreive the new record in the saved object.
            // This record has the uid equal to 0. The uid is replaced by the new created object.
            // Finally the relation is updated.
            unset($uncompressedParameters['subformUidForeign']);

            // Adds a new object in the foreign repository
            $subform = $this->getSubform($uncompressedParameters['subformKey']);
            $subformForeignRepository = $this->objectManager->get($subform['foreignRepository']);
            $objectToInsert = $subformForeignRepository->createModelObject();
            $subformForeignRepository->add($objectToInsert);
            $subformForeignRepository->persistAll();
            $objectToInsert = $subformForeignRepository->findByIdentifier($objectToInsert);
            $uid = $objectToInsert->getUid();

            // Finds the new object in the saved data from its path and
            // sets the uid to the new created object
            $newSubformItemPath = $arguments['newSubformItemPath'];
            $matches = [];
            preg_match_all('/(\w+)\.(-?\d+)/', $newSubformItemPath, $matches);
            if (! empty($matches[0])) {
                $objectToInsert = $data;
                foreach ($matches[0] as $matchKey => $match) {
                    $fieldName = $matches[1][$matchKey];
                    $getterName = 'get' . ucfirst($fieldName);
                    foreach ($objectToInsert->$getterName() as $item) {
                        if ($item->getUid() == $matches[2][$matchKey]) {
                            $objectToInsert = $item;
                            break;
                        }
                    }
                }
                $objectToInsert->setUid($uid);
            }

            // Updates the subform relations
            $subformRepository = $this->objectManager->get($subform['repository']);
            $object = $subformRepository->findByUid($uncompressedParameters['subformUidLocal']);
            $adderName = 'add' . $subform['fieldName'];

            // Adds the relations
            foreach ($object->_getCleanProperty(lcfirst($subform['fieldName'])) as $cleanProperty) {
                $object->$adderName($cleanProperty);
            }
            $object->$adderName($objectToInsert);
            $subformRepository->update($object);
            $uid = $data->getUid();
        } else {
            // Updates the main repository
            $this->getMainRepository()->update($data);
            $uid = $data->getUid();
        }

        // Processes the save buttons
        $uncompressedParameters['uid'] = $uid;
        if (isset($arguments['save'])) {
            $action = 'edit';
        } elseif (isset($arguments['saveAndClose'])) {
            $action = 'list';
        } elseif (isset($arguments['saveAndShow'])) {
            $action = 'single';
        } elseif (isset($arguments['saveAndNew'])) {
            $uncompressedParameters['uid'] = 0;
            $action = 'edit';
        } else {
            $action = 'list';
        }

        $special = self::compressParameters($uncompressedParameters);

        // Clears the cache
        $pageUid = $GLOBALS['TSFE']->id;
        $this->cacheService->clearPageCache($pageUid);

        // Redirects to the action
        $this->redirect($action, null, null, [
            'special' => $special
        ]);
    }

    /**
     * Delete action
     *
     * @param string $special
     * @return void
     */
    public function deleteAction($special = null)
    {
        // Checks if the user is authenticated
        if ($this->getFrontendUserManager()->userIsAuthenticated() === false) {
            FlashMessages::addError('fatal.notAuthenticated');
            return $this->redirect('single', null, null, [
                'special' => $special
            ]);
        }

        // Uncompresses the special parameter
        $uncompressedParameters = self::uncompressParameters($special);

        // Gets the uid
        $uid = $uncompressedParameters['uid'];

        // Gets the object from the uid
        $object = $this->getMainRepository()->findByUid($uid);

        // Removes the object
        $this->getMainRepository()->remove($object);

        // Redirects to the list in edit mode action
        $this->redirect('list', null, null, [
            'special' => $special
        ]);
    }

    /**
     * Delete in subform action
     *
     * @param string $special
     * @return void
     */
    public function deleteInSubformAction($special = null)
    {
        // Checks if the user is authenticated
        if ($this->getFrontendUserManager()->userIsAuthenticated() === false) {
            FlashMessages::addError('fatal.notAuthenticated');
            return $this->redirect('single', null, null, [
                'special' => $special
            ]);
        }

        // Uncompresses the special parameter
        $uncompressedParameters = self::uncompressParameters($special);

        // Removes the object in the foreign repository
        $subform = $this->getSubform($uncompressedParameters['subformKey']);
        $subformForeignRepository = $this->objectManager->get($subform['foreignRepository']);
        $objectToRemove = $subformForeignRepository->findByUid($uncompressedParameters['subformUidForeign']);
        $subformForeignRepository->remove($objectToRemove);

        // Updates the subform relations
        // The code below makes it possible to update the relations.
        // However, the relations cannot be retreived anymore if the object is undeleted.
        // TODO may be make it optionnal by a configuration value.
        //
        // $subformRepository = $this->objectManager->get($subform['repository']);
        // $object = $subformRepository->findByUid($uncompressedParameters['subformUidLocal']);
        // $removerName = 'remove' . $subform['fieldName'];
        // $object->$removerName($objectToRemove);
        // $subformRepository->update($object);

        // Unsets the subform key and item uid
        unset($uncompressedParameters['subformKey']);
        unset($uncompressedParameters['subformUidForeign']);
        $special = self::compressParameters($uncompressedParameters);

        // Redirects to the list in edit mode action
        $this->redirect('edit', null, null, [
            'special' => $special
        ]);
    }

    /**
     * Down in subform action
     *
     * @param string $special
     * @return void
     */
    public function downInSubformAction($special = null)
    {
        // Checks if the user is authenticated
        if ($this->getFrontendUserManager()->userIsAuthenticated() === false) {
            FlashMessages::addError('fatal.notAuthenticated');
            return $this->redirect('single', null, null, [
                'special' => $special
            ]);
        }

        // Uncompresses the special parameter
        $uncompressedParameters = self::uncompressParameters($special);

        // Gets the object to move in the foreign repository
        $subform = $this->getSubform($uncompressedParameters['subformKey']);
        $subformForeignRepository = $this->objectManager->get($subform['foreignRepository']);
        $objectToMove = $subformForeignRepository->findByUid($uncompressedParameters['subformUidForeign']);

        // Gets a new storage and moves the object in and copies it back to the current storage
        $subformRepository = $this->objectManager->get($subform['repository']);
        $object = $subformRepository->findByUid($uncompressedParameters['subformUidLocal']);
        $getterName = 'get' . $subform['fieldName'];
        $storage = $this->objectManager->get(ObjectStorage::class);
        $storage->addAll($object->$getterName());
        $storage->moveDown($objectToMove);
        $object->$getterName()->removeAll($storage);
        $object->$getterName()->addAll($storage);
        $subformRepository->update($object);

        // Unsets the subform key and item uid
        unset($uncompressedParameters['subformKey']);
        unset($uncompressedParameters['subformUidForeign']);
        unset($uncompressedParameters['subformUidLocal']);
        $special = self::compressParameters($uncompressedParameters);

        // Redirects to the list in edit mode action
        $this->redirect('edit', null, null, [
            'special' => $special
        ]);
    }

    /**
     * Down in subform action
     *
     * @param string $special
     * @return void
     */
    public function upInSubformAction($special = null)
    {
        // Checks if the user is authenticated
        if ($this->getFrontendUserManager()->userIsAuthenticated() === false) {
            FlashMessages::addError('fatal.notAuthenticated');
            return $this->redirect('single', null, null, [
                'special' => $special
            ]);
        }

        // Uncompresses the special parameter
        $uncompressedParameters = self::uncompressParameters($special);

        // Gets the object to move in the foreign repository
        $subform = $this->getSubform($uncompressedParameters['subformKey']);
        $subformForeignRepository = $this->objectManager->get($subform['foreignRepository']);
        $objectToMove = $subformForeignRepository->findByUid($uncompressedParameters['subformUidForeign']);

        // Gets the storage and moves the object
        $subformRepository = $this->objectManager->get($subform['repository']);
        $object = $subformRepository->findByUid($uncompressedParameters['subformUidLocal']);
        $getterName = 'get' . $subform['fieldName'];
        $storage = $this->objectManager->get(ObjectStorage::class);
        $storage->addAll($object->$getterName());
        $storage->moveUp($objectToMove);
        $object->$getterName()->removeAll($storage);
        $object->$getterName()->addAll($storage);
        $subformRepository->update($object);

        // Unsets the subform key and item uid
        unset($uncompressedParameters['subformKey']);
        unset($uncompressedParameters['subformUidForeign']);
        unset($uncompressedParameters['subformUidLocal']);
        $special = self::compressParameters($uncompressedParameters);

        // Redirects to the list in edit mode action
        $this->redirect('edit', null, null, [
            'special' => $special
        ]);
    }

    /**
     * Delete file action
     *
     * @param string $special
     * @return void
     */
    public function deleteFileAction($special = null)
    {
        // Checks if the user is authenticated
        if ($this->getFrontendUserManager()->userIsAuthenticated() === false) {
            FlashMessages::addError('fatal.notAuthenticated');
            return $this->redirect('single', null, null, [
                'special' => $special
            ]);
        }

        // Uncompresses the special parameter
        $uncompressedParameters = self::uncompressParameters($special);

        // Gets the file reference object
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        $fileReferenceUid = intval($uncompressedParameters['fileUid']);
        $fileReferenceObject = $resourceFactory->getFileReferenceObject($fileReferenceUid);

        // Deletes the file
        $fileReferenceObject->getOriginalFile()->delete();

        // Marks the file reference as delete
        GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_file_reference')->update('sys_file_reference', [
            'deleted' => 1
        ], // set
        [
            'uid' => $fileReferenceUid
        ] // where
        );

        // Redirects to the list in edit mode action
        $this->redirect('edit', null, null, [
            'special' => $special
        ]);
    }
}
?>