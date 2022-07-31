<?php
namespace Drupal\events\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Database;
use Drupal\events\Form\EventConfig;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Session\AccountInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * Provides 'Events List' Block
 * @Block(
 *  id="events_block",
 *  admin_label = @Translation("Events Block")
 * )
*/
class LatestEventsBlock extends BlockBase
{
    /**
   * {@inheritdoc}
   */
  public function build() {
    return [
        '#markup'=>$this->getLatestEvents(),
    ];
  }
   /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account)
  {
        return parent::blockAccess($account);
  
  }
   /**
   * {@inheritdoc}
   */
  private function getLatestEvents()
  {
        $stateValues = \Drupal::state()->get(EventConfig::CONFIG_VALUES);
        $connection = Database::getConnection();
        $query = $connection->select('events','e');
        $query->fields('e',['id','title']);
        $query->orderBy('id', 'DESC');
        if($stateValues['show_hide_past_events'] == 'hide')
        {
            $now = new DrupalDateTime('now');
            $query->condition('end_date', $now->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT), '>=');
        }
        $query->range(0,5);
        $result = $query->execute()->fetchAll();
        $events = [];
        foreach($result as $value)
        {
            
            $events[]=
                "<a href='/events/show/$value->id'> $value->title</a>".
                "</br>"
            ;
        }
        return implode(" ", $events);
  }
}