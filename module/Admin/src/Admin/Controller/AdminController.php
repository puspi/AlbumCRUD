<?php
namespace Admin\Controller;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Model\Admin;
use Admin\Form\AdminForm;



class AdminController extends AbstractActionController
{

    private $adminTable;
    //CRUD
    //R- Retrieve
    public function indexAction()
    {
        return new ViewModel(array(
            'admins' => $this->getAdminTable()->fetchAll(),
        ));
    }

    //C - Create
    public function addAction()
    {
        $form = new AdminForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $admin = new Admin();
            $form->setInputFilter($admin->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $admin->exchangeArray($form->getData());
                $this->getAdminTable()->saveAdmin($admin);

                // Redirect to list of albums
                return $this->redirect()->toRoute('admin');
            }
        }
        return array('form' => $form);
    }


    //U - Update
    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin', array('controller'=>'admin',
                'action' => 'edit'
            ));
        }

        // Get the Album with the specified id.  An exception is thrown
        // if it cannot be found, in which case go to the index page.
        try {
            $admin = $this->getAdminTable()->getAdmin($id);
        }
        catch (\Exception $ex) {
            return $this->redirect()->toRoute('admin', array(
                'action' => 'index'
            ));
        }

        $form  = new AdminForm();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($admin->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $this->getUsersTable()->update($data, array('id' =>$this->id));

                // Redirect to list of albums
                return $this->redirect()->toRoute('admin');
            }
        }

        return array(
            'id' => $id,
            'form' => $form,
        );
    }

    //D - Delete
    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $this->getAdminTable()->deleteAdmin($id);
            }

            // Redirect to list of albums
            return $this->redirect()->toRoute('admin');
        }

        return array(
            'id'    => $id,
            'admin' => $this->getAdminTable()->getAdmin($id)
        );
    }

    public function getAdminTable()
    {
        if (!$this->adminTable) {
            $sm = $this->getServiceLocator();
            $this->adminTable = $sm->get('Admin\Model\AdminTable');
        }
        return $this->adminTable;
    }



}
