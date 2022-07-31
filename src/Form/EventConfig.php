<?php
namespace Drupal\events\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class EventConfig extends FormBase
{
    const CONFIG_VALUES = 'event_config:values';
    const CONFIG_DEFAULT_NUMBERS = 5;

    public function getFormId()
    {
     return 'event_config';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $stateValues = \Drupal::state()->get(self::CONFIG_VALUES);
        $form=[];
        $form['number_of_events']=[
            '#type'=>'number',
            '#title'=> $this->t('The Number Of Events'),
            '#description'=> $this->t('The Number Of Events You Want To Show'),
            '#required'=>TRUE,
            '#default_value'=>  $stateValues['number_of_events'] ??  self::CONFIG_DEFAULT_NUMBERS
        ];
        $form['show_hide_past_events']=[
            '#type' => 'radios',
            '#title' => $this->t('Past Events'),
            '#default_value'=>$stateValues['show_hide_past_events'] ?? 'show',
            '#options' => array(
              'show' => $this->t('Show Past Events'),
              'hide' => $this->t('Hide Past Events'),
            ),
        ];
        $form['actions']['#type']= 'actions';
        $form['actions']['submit']=[
            '#type'=>'submit',
            '#value'=>'Save',
            '#button_type'=>'primary'
        ];
        return $form;
    }
    
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $submitted_values = $form_state->cleanValues()->getValues();
        \Drupal::state()->set(self::CONFIG_VALUES,$submitted_values);

        $messenger = \Drupal::service('messenger');
        $messenger->addMessage($this->t('Your New Configuration Has Been Saved')); 
    }
}