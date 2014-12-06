<?php
namespace Craft;

class EventBriteService extends BaseApplicationComponent {

  public function apiGet($url, $params = '') {

    $plugin = craft()->plugins->getPlugin('eventBrite');
    $settings = $plugin->getSettings();
    $token = $settings['token'];

    $options = array();
    //$cachedResponse = craft()->fileCache->get($url);
    $url = 'https://www.eventbriteapi.com'.$url.'?token='.$token;

    if ($params != '') {
      $url .= '&'.$params;
    }

    $client = new \Guzzle\Http\Client();
    $request = $client->get($url);
    $response = $request->send();

    if (!$response->isSuccessful()) {
      return;
    }

    return $response->json();
  }

  public function getStatus($event) {



	    $totalClasses = count($event["ticket_classes"]);

	    $soldOutClasses = 0;
	    $notYetClasses = 0;
	    $closedClasses = 0;

	    foreach($event["ticket_classes"] as $ticket) {

	      $salesStartDate = strtotime($ticket['sales_start']);
	      $salesEndDate = strtotime($ticket['sales_end']);

        $salesStartDate =  new \DateTime( date('Y-m-d h:i:s a',$salesStartDate), new \DateTimezone('Africa/Nairobi'));
        $salesStartDate->setTimezone(new \DateTimezone('America/New_York'));

        $salesEndDate =  new \DateTime( date('Y-m-d h:i:s a',$salesEndDate), new \DateTimezone('Africa/Nairobi'));
        $salesEndDate->setTimezone(new \DateTimezone('America/New_York'));

        $today = new \DateTime( date('Y-m-d h:i:s a'));

	      if ($salesStartDate > $today) {
	        $notYetClasses++;
	      } elseif ($salesEndDate < $today) {
	        $closedClasses++;
	      } else {
	        if ($ticket['quantity_total'] <= $ticket['quantity_sold']) {
	          $soldOutClasses++;
	        }
	      }
	    }

	    if ($soldOutClasses == $totalClasses) {
	      return 'soldout';
	    } elseif ($notYetClasses == $totalClasses) {
	      return 'notYet';
	    } elseif ($closedClasses == $totalClasses) {
	        return 'closed';
	    } else {
	      return 'live';
	    }

  }

  public function getSaleDate($event) {

    $today = strtotime('Today');
    $actionDate = strtotime('+1 year');
    foreach($event["ticket_classes"] as $ticket) {

      $salesStartDate = strtotime($ticket['sales_start']);



      if ($salesStartDate < $actionDate ) {
        $actionDate = $salesStartDate;
      }


    }

    $actionDate =  new \DateTime( date('Y-m-d h:i:s a',$actionDate), new \DateTimezone('Africa/Nairobi'));
	  $actionDate->setTimezone(new \DateTimezone('America/New_York'));

    //return $actionDate->format("Y-m-d h:i a");


	return $actionDate->format("l, F j").' at '.$actionDate->format("g:i a");
  }

  public function getEvent($eventId) {



    $event = craft()->eventBrite->apiGet('/v3/events/'.$eventId);



    $ticketStatus = craft()->eventBrite->getStatus($event);

    if ($ticketStatus == 'notYet') {

      $saleDate = craft()->eventBrite->getSaleDate($event);

      $registerButton = '<strong>Registration opens on '.$saleDate.'</strong>' ;

    } else if ($ticketStatus == 'closed') {

      $registerButton = '<strong>Registration closed</strong>' ;

    } else if ($ticketStatus == 'soldout') {

      $registerButton = '<a href="http://www.eventbrite.com/event/'.$eventId.'"><button class="btn btn-success">Join the WaitList</button></a>';

    } else {

      $registerButton = '<a href="http://www.eventbrite.com/event/'.$eventId.'"><button class="btn btn-success">Register</button></a>';

    }

    $eventModel = new EventBrite_EventModel();

    $eventModel->id = $event['id'];
    $eventModel->status = $ticketStatus;
    $eventModel->registerButton = TemplateHelper::getRaw($registerButton);

    return $eventModel;


  }
}
