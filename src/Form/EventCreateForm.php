<?php
namespace Drupal\events\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provide Event Form
 */
class EventCreateForm extends FormBase
{
    const FORM_NAME = 'events_create_form';

    const FORM_TITLE = 'title';
    const FORM_MEMO = 'memo';
    const FORM_CATEGORY = 'category';
    const FORM_START_DATE = 'start_date';
    const FORM_END_DATE = 'end_date';
    const FORM_IMAGE = 'image';
    
    const FORM_KEYS = [
        self::FORM_TITLE,
        self::FORM_MEMO,
        self::FORM_CATEGORY,
        self::FORM_START_DATE,
        self::FORM_END_DATE,
        self::FORM_IMAGE,
    ];
    /* {@inherit} */
    public function getFormId()
    {
        return self::FORM_NAME;
    }
    
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['#attributes']=['enctype'=>'multipart/form-data'];
        $form[self::FORM_TITLE]=[
            '#title'=>$this->t('Event Title'),
            '#type'=>'textfield',
            '#size'=>30,
            '#maxlength'=>30,
            '#description'=>$this->t('This Will Be Your Event Title'),
            '#required'=>TRUE,
            '#attribute' => array(
                'min'=>30,
              ),
        ];
        $form[self::FORM_CATEGORY]=[
            '#title'=>$this->t('Event Category'),
            '#type'=>'textfield',
            '#size'=>30,
            '#maxlength'=>30,
            '#description'=>$this->t('This Will Be Your Event Category'),
            '#required'=>TRUE,
        ];
        $form[self::FORM_MEMO]=[
            '#title'=>$this->t('Event Title'),
            '#type'=>'textarea',
            '#size'=>100,
            '#maxlength'=>100,
            '#description'=>$this->t('This Will Be Your Event Memo'),
            '#required'=>TRUE,
        ];
        $form[self::FORM_START_DATE]=[
            '#title'=>$this->t('Event Start Date'),
            '#type'=>'date',
            '#size'=>30,
            '#maxlength'=>30,
            '#description'=>$this->t('This Will Be Your Event Starting Date'),
            '#required'=>TRUE,
        ];
        $form[self::FORM_END_DATE]=[
            '#title'=>$this->t('Event End Date'),
            '#type'=>'date',
            '#size'=>30,
            '#description'=>$this->t('This Will Be Your Event Ending Date'),
            '#required'=>TRUE,
        ];
        $form['add'][self::FORM_IMAGE]=[
            '#title'=>$this->t('Event Image'),
            '#description'=>$this->t('This Will Be Your Event Image'),
            '#required'=>TRUE,
            '#type' => 'managed_file',
            '#upload_location'=>'public://events_images',
            '#upload_validators' => array(
                'file_validate_extensions' => array('png jpg jpeg'),
                'file_validate_size' => array(25600000),
   ),
        ];
        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = [
          '#type' => 'submit',
          '#value' => $this->t('Save'),
          '#button_type' => 'primary',
        ];
        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        if(strtotime($form_state->getValue('start_date')) > strtotime($form_state->getValue('end_date')))
        {
            $form_state->setErrorByName(self::FORM_END_DATE,$this->t("The End Date has to be after Start Date"));

        }
    }
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $data=[];
        foreach(self::FORM_KEYS as $key)
        {
            $data[$key] = $form_state->getValue($key);
        }

        $image = $form_state->getValue(['add'=>self::FORM_IMAGE]);
        $file = \Drupal\file\Entity\File::load($image[0]);
        $file->setPermanent();
        $file->save();
        $data[self::FORM_IMAGE] = $file->getFileUri();
        $connection = \Drupal::service('database');
        $connection->insert('events')
        ->fields($data)
        ->execute();

        $this->messenger()->addMessage($this->t("the new event {$form_state->getValue('title')} was inserted successfully."));

    }

}
