<?php

class AlertsModel extends CI_Model
{
	var $title = '';
	var $content = '';
	var $date = '';
	var $author = '';

	function __construct()
	{
		//This calls the constructor of the Model.
		parent::__construct();
	}

	/*
		Get the last two alerts.
	*/
	function getLastTwoAlerts()
	{
		$query = $this->db->get('entries', 2);
		return $query->result();
	}

	/*
		Insert one alert into the database
	*/
	function insertAlert()
	{
		$this->title = $this->input->post('title');
		$this->content = $this->input->post('content');
		$this->author = $this->input->post('author');
		$this->date = time(); 
	
		$this->db->insert( 'alerts', $this );
	}

}

?>
