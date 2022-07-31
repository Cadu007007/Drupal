<?php
namespace Drupal\events\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provide Event Form
 */
class EventEditForm extends FormBase
{
    const FORM_NAME = 'events_edit_form';

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
    private $event;
    /* {@inherit} */
    public function getFormId()
    {
        return self::FORM_NAME;
    }
    
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $eventId = \Drupal::routeMatch()->getParameter('event') ?? 0;
        $query = \Drupal::database()->select('events', 'e');
        $query->fields('e', ['id','title','memo','category','start_date','end_date','image']);
        $query->condition('e.id', $eventId);
        $event = $query->execute()->fetchAssoc();
        $this->event = $event;

        if (!$event) {
            $form_state->setErrorByName('Event',$this->t("This Event Is not Exists"));

        }
        $form['#attributes']=['enctype'=>'multipart/form-data'];
        $form[self::FORM_TITLE]=[
            '#title'=>$this->t('Event Title'),
            '#type'=>'textfield',
            '#size'=>30,
            '#maxlength'=>30,
            '#description'=>$this->t('This Will Be Your Event Title'),
            '#required'=>TRUE,
            '#value'=>$event['title'],
            '#attribute' => array(
                'min'=>30,
              ),
        ];
        $form[self::FORM_CATEGORY]=[
            '#title'=>$this->t('Event Category'),
            '#type'=>'textfield',
            '#size'=>30,
            '#maxlength'=>30,
            '#value'=>$event['category'],
            '#description'=>$this->t('This Will Be Your Event Category'),
            '#required'=>TRUE,
        ];
        $form[self::FORM_MEMO]=[
            '#title'=>$this->t('Event Title'),
            '#type'=>'textarea',
            '#value'=>$event['memo'],
            '#size'=>100,
            '#maxlength'=>100,
            '#description'=>$this->t('This Will Be Your Event Memo'),
            '#required'=>TRUE,
        ];
        $form[self::FORM_START_DATE]=[
            '#title'=>$this->t('Event Start Date'),
            '#type'=>'date',
            '#size'=>30,
            '#value'=>$event['start_date'],
            '#maxlength'=>30,
            '#description'=>$this->t('This Will Be Your Event Starting Date'),
            '#required'=>TRUE,
        ];
        // $form['#div'] = '<div>Suffix to form</div>';
        
        $form[self::FORM_END_DATE]=[
            '#title'=>$this->t('Event End Date'),
            '#type'=>'date',
            '#value'=>$event['end_date'],
            '#size'=>30,
            '#description'=>$this->t('This Will Be Your Event Ending Date'),
            '#required'=>TRUE,
        ];

        $image = file_create_url($event['image']);
        $form['box'] = array(
            '#type' => 'markup',
            '#suffix' => '<div> Event Image</div>',
            '#markup' => '<img src="'.$image.'" alt="'.$event['title'].'" width="25%" height="25%">',
        );
        $form['edit'][self::FORM_IMAGE]=[
            '#title'=>$this->t('Event Image'),
            '#description'=>$this->t('By Uploading New Image The Old One Will Be Deleted'),
            '#required'=>FALSE,
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
          '#value' => $this->t('Update'),
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
            $data[$key] = \Drupal::request()->request->get($key);
        }
        
        if($form_state->getValue('image'))
        {
            $path = \Drupal::service('file_system')->realpath($this->event['image']);
            unlink($path);

            $image = $form_state->getValue(['edit'=>self::FORM_IMAGE]);
            $file = \Drupal\file\Entity\File::load($image[0]);
            $file->setPermanent();
            $file->save();
            $data[self::FORM_IMAGE] = $file->getFileUri();
        }
        $connection = \Drupal::database();
        $connection->update('events')
        ->fields($data)
        ->condition('id',$this->event['id'])
        ->execute();
        $this->messenger()->addMessage($this->t("the event {$form_state->getValue('title')} was Updated successfully."));

    }

}
