<?php
namespace Drupal\events\Controller;

use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Database\Database;
use Drupal\events\Form\EventConfig;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Controller\ControllerBase;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

class EventController extends ControllerBase
{
    /* get the event with id to show to the user */
    public function view()
    {
        $eventId = \Drupal::routeMatch()->getParameter('id') ?? 0;
        $query = \Drupal::database()->select('events', 'e');
        $query->fields('e', ['id','title','memo','category','start_date','end_date','image']);
        $query->condition('e.id', $eventId);
        $event = $query->execute()->fetchAssoc();
        if (!$event) {
        $this->messenger()->addError($this->t("the event not exists."));
        }
        $event['image'] = file_create_url($event['image']);
        return [
            '#theme'=>'event-item',
            '#event'=>$event
         ];
    }
    /* get filtered events to users according to configruations */
    public function index()
    {
        $stateValues = \Drupal::state()->get(EventConfig::CONFIG_VALUES);

       
        $connection = Database::getConnection();
        $query = $connection->select('events','e');
        $query->fields('e',['id','title','image','start_date','end_date']);
        if($stateValues['number_of_events'])
        {
            $query->range(0,$stateValues['number_of_events']);
        }
        else{
            
            $query->range(0,EventConfig::CONFIG_DEFAULT_NUMBERS);
        }
        if($stateValues['show_hide_past_events'] == 'hide')
        {
            $now = new DrupalDateTime('now');
            $query->condition('end_date', $now->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT), '>=');
        }
        
        $result = $query->execute()->fetchAll();
        $data = [];
        foreach($result as $value)
        {
            $data[]=['id'=>$value->id,
            'title'=>$value->title,
            'start_date'=>$value->start_date,
            'end_date'=>$value->end_date,
            'image'=>file_create_url($value->image),
            'url'=>Url::fromUserInput('/events/show/'.$value->id)
            ] ;
        }
        return [
           '#theme'=>'event-listing',
           '#events'=>$data
        ];
    }

    /* get all events to show in admin to manage it */
    public function adminIndex()
    {

        $header_table = [
            'id'=>$this->t('ID'),
            'title'=>$this->t('Title'),
            'opt'=>$this->t('Edit'),
            'opt1'=>$this->t('Delete')
        ];
        $rows=[];
        $connection = Database::getConnection();
        $query = $connection->select('events','e');
        $query->fields('e',['id','title']);     
        $result = $query->execute()->fetchAll();
        foreach($result as $value)
        {
            $delete = Url::fromUserInput('/admin/events/delete/'.$value->id);
            $edit = Url::fromUserInput('/admin/events/edit/'.$value->id);
            $rows[] =['id'=>$value->id,'title'=>$value->title,
            'opt'=>Link::fromTextAndUrl('Edit',$edit)->toString(),
            'opt1'=>Link::fromTextAndUrl('delete',$delete)->toString()
            ] ;
        }
        $add = Url::fromUserInput('/admin/events/create');
        $text = 'Add New Event';
        $data['table']=['#type'=>'table',
        '#header'=>$header_table,'#rows'=>$rows,
        '#empty'=>$this->t('No Data Available'),
        '#caption'=>Link::fromTextAndUrl($text,$add)->toString()];
        return $data;
        
    }
}