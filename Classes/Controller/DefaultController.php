<?php
namespace SAV\SavLibraryMvc\Controller;

/**
 * Copyright notice
 *
 * (c) 2015 Laurent Foulloy <yolf.typo3@orange.fr>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */
use SAV\SavLibraryMvc\Controller\FlashMessages;
use SAV\SavLibraryMvc\Persistence\ObjectStorage;

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
    public function listAction($special = NULL)
    {
        // Checks if the user is authenticated
        $uncompressedParameters = self::uncompressParameters($special);
        $mode = $uncompressedParameters['mode'];

        $arguments = array(
            'special' => $special
        );
        $viewConfiguration = $this->getViewConfiguration($arguments);
        $this->view->assign('general', $viewConfiguration['general']);
        $this->view->assign('items', $viewConfiguration['items']);
    }

    /**
     * Single action
     *
     * @param string $special
     * @return void
     */
    public function singleAction($special = NULL)
    {
        $arguments = array(
            'special' => $special
        );
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
    public function editAction($special = NULL)
    {
        // Checks if the user is authenticated
        if ($this->getFrontendUserManager()->userIsAuthenticated() === FALSE) {
            FlashMessages::addError('fatal.notAuthenticated');
            return $this->redirect('single', NULL, NULL, array(
                'special' => $special
            ));
        }

        $arguments = array(
            'special' => $special
        );
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
        if ($this->getFrontendUserManager()->userIsAuthenticated() === FALSE) {
            FlashMessages::addError('fatal.notAuthenticated');
            return $this->redirect('single', NULL, NULL, array(
                'special' => $special
            ));
        }

        // Gets the arguments
        $arguments = $this->request->getArguments();

        // Adds the current page to the stack in the cache service if not in the storage pages
        if (! in_array($GLOBALS['TSFE']->id, self::getStoragePages())) {
            $this->cacheService->getPageIdStack()->push($GLOBALS['TSFE']->id);
        }

        $isNewItemInSubform = isset($uncompressedParameters['subformUidForeign']) && $uncompressedParameters['subformUidForeign'] == - 1;

        // Updates the data
        if (is_null($data->getUid())) {
            // New record in the main form. Creates a new object in the main repository and gets its uid
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
            preg_match_all('/(\w+)\.(-?\d+)/', $arguments['newSubformItemPath'], $matches);
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

        // Redirects to the action
        $this->redirect($action, NULL, NULL, array(
            'special' => $special
        ));
    }

    /**
     * Delete action
     *
     * @param string $special
     * @return void
     */
    public function deleteAction($special = NULL)
    {
        // Checks if the user is authenticated
        if ($this->getFrontendUserManager()->userIsAuthenticated() === FALSE) {
            \SAV\SavLibraryMvc\Controller\FlashMessages::addError('fatal.notAuthenticated');
            return $this->redirect('single', NULL, NULL, array(
                'special' => $special
            ));
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
        $this->redirect('list', NULL, NULL, array(
            'special' => $special
        ));
    }

    /**
     * Delete in subform action
     *
     * @param string $special
     * @return void
     */
    public function deleteInSubformAction($special = NULL)
    {
        // Checks if the user is authenticated
        if ($this->getFrontendUserManager()->userIsAuthenticated() === FALSE) {
            \SAV\SavLibraryMvc\Controller\FlashMessages::addError('fatal.notAuthenticated');
            return $this->redirect('single', NULL, NULL, array(
                'special' => $special
            ));
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
        $this->redirect('edit', NULL, NULL, array(
            'special' => $special
        ));
    }

    /**
     * Down in subform action
     *
     * @param string $special
     * @return void
     */
    public function downInSubformAction($special = NULL)
    {
        // Checks if the user is authenticated
        if ($this->getFrontendUserManager()->userIsAuthenticated() === FALSE) {
            FlashMessages::addError('fatal.notAuthenticated');
            return $this->redirect('single', NULL, NULL, array(
                'special' => $special
            ));
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
        $this->redirect('edit', NULL, NULL, array(
            'special' => $special
        ));
    }

    /**
     * Down in subform action
     *
     * @param string $special
     * @return void
     */
    public function upInSubformAction($special = NULL)
    {
        // Checks if the user is authenticated
        if ($this->getFrontendUserManager()->userIsAuthenticated() === FALSE) {
            FlashMessages::addError('fatal.notAuthenticated');
            return $this->redirect('single', NULL, NULL, array(
                'special' => $special
            ));
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
        $this->redirect('edit', NULL, NULL, array(
            'special' => $special
        ));
    }
}

?>