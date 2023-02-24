<?php

declare(strict_types=1);

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

namespace YolfTypo3\SavLibraryMvc\Controller;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use YolfTypo3\SavLibraryMvc\Domain\Repository\AbstractRepository;
use YolfTypo3\SavLibraryMvc\Domain\Model\Export;
use YolfTypo3\SavLibraryMvc\Persistence\ObjectStorage;

/**
 * Default controller for the SAV Library MVC
 */
class DefaultController extends AbstractController
{

    /**
     * List action
     *
     * @param string|null $special
     * @return void
     */
    public function listAction(?string $special = null)
    {
        $arguments = [
            'special' => $special
        ];
        $viewConfiguration = $this->getViewConfiguration($arguments);

        // Sets the view parameters
        $this->view->assign('general', $viewConfiguration['general']);
        $this->view->assign('items', $viewConfiguration['items']);

        // For TYPO3 V11: action must return an instance of Psr\Http\Message\ResponseInterface
        if (method_exists($this, 'htmlResponse')) {
            return $this->htmlResponse($this->view->render());
        }
    }

    /**
     * Single action
     *
     * @param string|null $special
     * @return void
     */
    public function singleAction(?string $special = null)
    {
        $arguments = [
            'special' => $special
        ];
        $viewConfiguration = $this->getViewConfiguration($arguments);

        // Sets the view parameters
        $this->view->assign('general', $viewConfiguration['general']);
        $this->view->assign('fields', $viewConfiguration['fields']);
        $this->view->assign('folders', $viewConfiguration['folders']);

        // For TYPO3 V11: action must return an instance of Psr\Http\Message\ResponseInterface
        if (method_exists($this, 'htmlResponse')) {
            return $this->htmlResponse($this->view->render());
        }
    }

    /**
     * Edit action
     *
     * @param string|null $special
     * @return void
     */
    public function editAction(?string $special = null)
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

        // For TYPO3 V11: action must return an instance of Psr\Http\Message\ResponseInterface
        if (method_exists($this, 'htmlResponse')) {
            return $this->htmlResponse($this->view->render());
        }
    }

    /**
     * Export action
     *
     * @param string|null $special
     * @return void
     */
    public function exportAction(?string $special = null, bool $executeRequested = false)
    {
        // Checks if the user is allowed to export data
        if ($this->getFrontendUserManager()->userIsAllowedToExportData() === false) {
            FlashMessages::addError('fatal.notAllowedToExportData');
            return $this->redirect('list', null, null, [
                'special' => $special
            ]);
        }

        $arguments = [
            'special' => $special,
            'executeRequested' => $executeRequested
        ];
        $viewConfiguration = $this->getViewConfiguration($arguments);

        // Sets the view parameters
        $this->view->assign('general', $viewConfiguration['general']);

        // For TYPO3 V11: action must return an instance of Psr\Http\Message\ResponseInterface
        if (method_exists($this, 'htmlResponse')) {
            return $this->htmlResponse($this->view->render());
        }
    }

    /**
     * ExportSubmit action
     *
     * @param Export|null $data
     * @return void
     */
    public function exportSubmitAction(?Export $data = null)
    {
        // Gets the arguments
        $arguments = $this->request->getArguments();

        // Uncompresses the special arguments
        $special = $arguments['special'];

        // Checks if the user is allowed to export data
        if ($this->getFrontendUserManager()->userIsAllowedToExportData() === false) {
            FlashMessages::addError('fatal.notAllowedToExportData');
        }

        // Uncompresses the special arguments
        $uncompressedParameters = self::uncompressParameters($special);

        if (isset($arguments['exportUid'])) {
            $uncompressedParameters['exportUid'] = $arguments['exportUid'];
            $special = self::compressParameters($uncompressedParameters);
        }

        if (isset($arguments['exportLoad'])) {
            $this->redirect('export', null, null, [
                'special' => $special
            ]);
        }

        if (is_null($data->getUid())) {
            $this->exportRepository->add($data);
            $this->exportRepository->persistAll();
        } else {
            $this->exportRepository->update($data);
        }


        $uncompressedParameters['exportUid'] = $data->getUid();
        $special = self::compressParameters($uncompressedParameters);

        if (isset($arguments['save'])) {
            $this->redirect('export', null, null, [
                'special' => $special
            ]);
        } else {
            $this->redirect('export', null, null, [
                'special' => $special,
                'executeRequested' => true
            ]);
        }
    }

    /**
     * Save method called by saveAction in the controller class
     *
     * @param AbstractEntity $data
     * @return void
     */
    public function save($data)
    {
        // Gets the arguments
        $arguments = $this->request->getArguments();

        // Uncompresses the special arguments
        $special = $arguments['special'];
        $uncompressedParameters = self::uncompressParameters($special);

        // Checks if the user is authenticated
        if ($this->getFrontendUserManager()->userIsAuthenticated() === false) {
            FlashMessages::addError('fatal.notAuthenticated');
            return $this->redirect('single', null, null, [
                'special' => $special
            ]);
        }

        // Checks if the item is a new one in subform
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
            $identifier = $this->getMainRepository()->getIdentifierByObject($data);
            $object = $this->getMainRepository()->findByIdentifier($identifier);
            $uid = $object->getUid();
        } elseif ($isNewItemInSubform) {
            // New record in subform.
            // The choice was to create the new object when saving and not when clicking on
            // the new icon. By doing so, the record is created only if saved.
            unset($uncompressedParameters['subformUidForeign']);

            // Updates the subform fields
            $this->updateSubformFields($data, $uncompressedParameters['subformUidLocal']);

            // Updates the main repository
            $this->getMainRepository()->update($data);
            $this->getMainRepository()->persistAll();

            $uid = $data->getUid();
        } else {
            // Updates the subform fields
            $this->updateSubformFields($data);

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
        } elseif ($arguments['action'] == 'save') {
            $action = 'edit';
        } else {
            $action = 'list';
        }

        $special = self::compressParameters($uncompressedParameters);

        // Clears the cache
        $this->cacheService->clearPageCache($this->getPageId());

        // Redirects to the action
        $this->redirect($action, null, null, [
            'special' => $special
        ]);
    }

    /**
     * Updates the subform fields
     *
     * @param AbstractEntity $data
     * @return void
     */
    protected function updateSubformFields($data, $subformUidLocal = null)
    {
        // Gets the associated repository class name
        $repositoryClassName = str_replace('\\Model\\', '\\Repository\\', get_class($data)) . 'Repository';
        $repository = GeneralUtility::makeInstance($repositoryClassName);
        $repository->setController($this);

        $items = $data->_getProperties();
        foreach ($items as $itemKey => $item) {

            // Gets the field type from the repository datamap factory
            $fieldName = GeneralUtility::camelCaseToLowerCaseUnderscored($itemKey);
            $dataMapFactory = $repository->getDataMapFactory();
            $fieldType = $dataMapFactory->getFieldType($fieldName);
            $tcaFieldConfiguration = $dataMapFactory->getTCAFieldConfiguration($fieldName);
            if ($fieldType == 'RelationManyToManyAsDoubleSelectorbox' &&
                 !empty($tcaFieldConfiguration['MM'])) {
                if ($repository != $this->getMainRepository()) {
                    $propertyName = ucfirst($itemKey);
                    $setterName = 'set' . $propertyName;
                    $object = $repository->findByUid($data->getUid());
                    $object->$setterName($item);
                    $repository->update($object);
                    $unsetterName = 'unset' . $propertyName;
                    $data->$unsetterName();
                }
            } elseif ($fieldType == 'RelationManyToManyAsSubform') {
                // find the foreign repository
                $subform = $this->getSubformFromFieldName($fieldName);
                $foreignRepository = GeneralUtility::makeInstance($subform['foreignRepository']);

                $getterName = 'get' . ucfirst($itemKey);
                $unsetterName = 'unset' . ucfirst($itemKey);

                $dataItems= $data->$getterName();
                foreach($dataItems as $dataItemKey => $dataItem) {
                    // Checks if this is a new item
                    if ($dataItem->getUid() === null) {
                        // Retreives the subform content
                        $subformRepository =  GeneralUtility::makeInstance($subform['repository']);
                        $object = $subformRepository->findByUid($subformUidLocal);
                        // Adds the relations
                        $setterName = 'set' . ucfirst($itemKey);
                        $adderName = 'add' . ucfirst($itemKey);
                        $object->$setterName($object->_getCleanProperty(lcfirst($itemKey)));
                        $object->$adderName($dataItem);
                    } else {
                        $this->updateSubformFields($dataItem, $subformUidLocal);
                        $foreignRepository->add($dataItem);
                        $data->$unsetterName();
                        $data->_memorizeCleanState($itemKey);
                    }
                }
            }
        }
    }

    /**
     * Delete action
     *
     * @param string|null $special
     * @return void
     */
    public function deleteAction(?string $special = null)
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

        // Clears the cache
        $this->cacheService->clearPageCache($this->getPageId());

        // Redirects to the list in edit mode action
        $this->redirect('list', null, null, [
            'special' => $special
        ]);
    }

    /**
     * Delete in subform action
     *
     * @param string|null $special
     * @return void
     */
    public function deleteInSubformAction(?string $special = null)
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
        $subform = $this->getSubform((int) $uncompressedParameters['subformKey']);
        $subformForeignRepository = GeneralUtility::makeInstance($subform['foreignRepository']);
        $objectToRemove = $subformForeignRepository->findByUid($uncompressedParameters['subformUidForeign']);
        $subformForeignRepository->remove($objectToRemove);

        // Updates the subform relations
        // The code below makes it possible to update the relations.
        // However, the relations cannot be retreived anymore if the object is undeleted.
        // TODO may be make it optionnal by a configuration value.
        //
        // $subformRepository = GeneralUtility::makeInstance($subform['repository']);
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
     * @param string|null $special
     * @return void
     */
    public function downInSubformAction(?string $special = null)
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
        $subform = $this->getSubform((int) $uncompressedParameters['subformKey']);
        $subformForeignRepository = GeneralUtility::makeInstance($subform['foreignRepository']);
        $objectToMove = $subformForeignRepository->findByUid($uncompressedParameters['subformUidForeign']);

        // Gets a new storage and moves the object in and copies it back to the current storage
        $subformRepository = GeneralUtility::makeInstance($subform['repository']);
        $object = $subformRepository->findByUid($uncompressedParameters['subformUidLocal']);
        $propertyName = GeneralUtility::underscoredToUpperCamelCase($subform['fieldName']);
        $getterName = 'get' . $propertyName;
        $storage = GeneralUtility::makeInstance(ObjectStorage::class);
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
     * @param string|null $special
     * @return void
     */
    public function upInSubformAction(?string $special = null)
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
        $subform = $this->getSubform((int) $uncompressedParameters['subformKey']);
        $subformForeignRepository = GeneralUtility::makeInstance($subform['foreignRepository']);
        $objectToMove = $subformForeignRepository->findByUid($uncompressedParameters['subformUidForeign']);

        // Gets the storage and moves the object
        $subformRepository = GeneralUtility::makeInstance($subform['repository']);
        $object = $subformRepository->findByUid($uncompressedParameters['subformUidLocal']);
        $propertyName = GeneralUtility::underscoredToUpperCamelCase($subform['fieldName']);
        $getterName = 'get' . $propertyName;
        $storage = GeneralUtility::makeInstance(ObjectStorage::class);
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
     * @param string|null $special
     * @return void
     */
    public function deleteFileAction(?string $special = null)
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