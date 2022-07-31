<?php
namespace Drupal\events\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Messenger;
class EventDeleteForm extends ConfirmFormBase
{
    protected $cid;

    public function getFormId()
    {
        return 'event_delete_form';
    }
    public function getQuestion()
    {
        return $this->t('Delete The Record?.');   
    }
    public function getCancelUrl()
    {
        return new Url('events.admin.list');
    }
    public function getDescription()
    {
        return $this->t('Are You Sure You Want Delete The Record?.');   
        
    }

    public function getConfirmText()
    {
        return $this->t('Delete It');   
    }

    public function getCancelText()
    {
        return $this->t('No!');   
    }

    public function buildForm(array $form, FormStateInterface $form_state,$cid = null)
    {
            $this->id =  $cid;
            return parent::buildForm($form,$form_state);
    }

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        parent::validateForm($form,$form_state);
      
    }
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        
        $query = \Drupal::database()->select('events', 'e');
        $query->fields('e', ['id','image']);
        $query->condition('e.id', $this->id);
        $event = $query->execute()->fetchAssoc();
        $path = \Drupal::service('file_system')->realpath($event['image']);
        unlink($path);
        
        // delete record in db
        $query = \Drupal::database();
        $query->delete('events')->condition('id',$this->id)->execute();
        
        $this->messenger()->addMessage('Record Deleted Successfully');
        $form_state->setRedirect('events.admin.list');
    }

}