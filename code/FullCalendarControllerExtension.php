<?php
/**
 * Renders the calendar with the fullcalendar template, and returns the JSON
 * events data.
 *
 * @package silverstripe-fullcalendar
 */
class FullCalendarControllerExtension extends Extension {

	private static $allowed_actions = array(
		'full',
		'eventsdata'
	);


	public function onAfterInit(){
		$request = $this->owner->getRequest();

		if(!$request->param('Action') && $this->owner->data()->UseFullCalendar){
			return $this->owner->redirect($this->owner->Link('full'));
		}
	}

	/**
	 * @return full calendar view
	 */
	public function full() {
		Requirements::css('fullcalendar/thirdparty/jquery-fullcalendar/fullcalendar.min.css');

		Requirements::combine_files('fullcalendar.packed.js', array(
			'fullcalendar/thirdparty/jquery-fullcalendar/moment.min.js',
			'fullcalendar/thirdparty/jquery-fullcalendar/fullcalendar.min.js',
			'fullcalendar/javascript/FullCalendar.js',
		));
		// Requirements::javascript('fullcalendar/thirdparty/jquery-fullcalendar/moment.min.js');
		// Requirements::javascript('fullcalendar/thirdparty/jquery-fullcalendar/fullcalendar.min.js');
		// Requirements::javascript('fullcalendar/javascript/FullCalendar.js');
		Requirements::add_i18n_javascript('fullcalendar/thirdparty/jquery-fullcalendar/lang');

		$basicAgenda = 'agenda';

		switch ($this->owner->data()->DefaultView) {
			case 'today':
				$view = $basicAgenda . 'Day';
			case 'week':
				$view = $basicAgenda . 'Week';
				break;
			default:
				$view = 'month';
				break;
		}
		return $this->owner->customise(array(
			'FullCalendarView' => $view
		));
	}

	/**
	 * Handles returning the JSON events data for a time range.
	 *
	 * @param  SS_HTTPRequest $request
	 * @return SS_HTTPResponse
	 */
	public function eventsdata($request) {
		$start = $request->getVar('start');
		$end   = $request->getVar('end');

		// for testing
		// if(!$end){
		// 	$end = '2013-12-12';
		// }

		$events = $this->owner->data()->getEventList(
	      	sfDate::getInstance($start)->date(),
	      	sfDate::getInstance($end)->date(),
	      	null,
	      	null
	    );

		$result = array();

		if ($events) foreach ($events as $event) {
			$result[] = array(
				'id'        => $event->ID,
				'title'     => $event->getTitle(),
				'start'     => "$event->StartDate $event->StartTime",
				'end'       => "$event->EndDate $event->EndTime",
				'startTime' => $event->getFormattedStartTime(),
          		'endTime'   => $event->getFormattedEndTime(),
				'allDay'    => (bool) $event->AllDay,
				'url'       => $event->Link(),
			);
		}

		$this->owner->getRequest()->addHeader('Content-Type', 'application/json');
		return Convert::array2json($result);
	}

}
