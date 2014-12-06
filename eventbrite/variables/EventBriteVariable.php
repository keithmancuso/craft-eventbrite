<?php
namespace Craft;

class EventBriteVariable
{
    
    public function getTicketStatus($eventId)
    {

      $event = craft()->eventBrite->apiGet('/v3/events/'.$eventId);

      $ticketStatus = 'live';
      foreach($event["ticket_classes"] as $ticket) {

        if ($ticket['quantity_total'] <= $ticket['quantity_sold']) {
          $ticketStatus = 'sold out';
        }

      }

      return $ticketStatus;
    }

    public function getAllStatuses()
    {

      $events = craft()->eventBrite->apiGet('/v3/users/me/owned_events/');
      $events = $events['events'];

      $ticketStatuses = array();

      foreach($events as $event) {

        $thisStatus = array('id'=> $event['id'], 'status'=>'live' );
        foreach($event["ticket_classes"] as $ticket) {

          if ($ticket['quantity_total'] <= $ticket['quantity_sold']) {
            $thisStatus['status'] = 'sold out';
          }

        }
        $ticketStatuses[] = $thisStatus;
      }

      return $ticketStatuses;
    }


}
