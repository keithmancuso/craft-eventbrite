<?php
namespace Craft;

class EventBrite_EventsDropdownFieldType extends BaseFieldType
{
    public function getName()
    {
        return Craft::t('Eventbrite - Events Dropdowns');
    }

    public function getInputHtml($name, $value)
    {

	    $cachedResponse = craft()->fileCache->get('liveEvents');

	    if ($cachedResponse) {

		    $events = $cachedResponse;

	    } else {

		    $liveevents = craft()->eventBrite->apiGet('/v3/users/me/owned_events/', 'status=live&order_by=start_desc');
			  $events = $liveevents['events'];
      	craft()->fileCache->set('liveEvents', $events, 3600);

	    }

  
      //var_dump($liveevents);

      $options[] = array('label'=> 'No Eventbrite Link', 'value'=> '');

      foreach($events as $event) {

        $startDate = strtotime($event['start']['local']);
        $startDate = date("F j, Y", $startDate);

        $label = $event['name']['text'].' - '.$startDate;
        $options[] = array('label'=> $label, 'value'=> $event['id']);

      }

			// Cache the response
			//craft()->fileCache->set($url, $items);

      return craft()->templates->render('eventbrite/input', array(
          'name'  => $name,
          'value' => $value,
          'options' => $options

      ));
    }

    public function prepValue($eventId)
    {
	  //return $eventId;

	  try {
	    if ($eventId != '') {
        	$event = craft()->eventBrite->getEvent($eventId);

			if($event)
			{
			return $event;
        	}
        } else {
	        return null;
        }
      }
      catch(\Exception $e)
      {
        Craft::log("Couldn't get event in field prepValue: ".$e->getMessage(), LogLevel::Info, true);

        return null;
      }

    }
}
