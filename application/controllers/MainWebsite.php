<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MainWebsite extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		
	}

	private function common_view($views = [], $vars = [])
	{
		/* you can call model here */
		
			$vars['CURRENT_METHOD'] = $this->router->fetch_method();
			$this->load->view('frontend/layout/header.php', $vars);
			
			if (is_array($views)) {
				foreach ($views as $view) {
					$this->load->view('frontend/' . $view, $vars);
				}
			} else {
				$this->load->view('frontend/' . $views, $vars);
			}
			$this->load->view('frontend/layout/footer.php', $vars);
	}	

	
	public function index()
	{
		$this->common_view('index');
	}

	public function excel_reader(){
		$file = $_FILES['sample_file']['tmp_name'];
        $filename = $_FILES['sample_file']['name'];
		$extension = pathinfo($filename, PATHINFO_EXTENSION);
		
        if (!empty($extension)) {
            if ($extension != 'csv' && $extension != 'xlsx' && $extension != 'XLSX') {
				$this->session->set_flashdata("error", "System Error, Only CSV and Xlsx files are allowed.");
                redirect(base_url('MainWebsite/'));
            }
        }
        if (!is_readable($file)) {
            $this->session->set_flashdata('error', "File is not readable.");
            redirect(base_url('MainWebsite/'));
		}	

        $objPHPExcel = PHPExcel_IOFactory::load($file);
        //get only the Cell Collection
        $cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
		echo "<pre>";
		
	
		$array=array();
        //extract to a PHP readable array format
        for($i=0;$i<count($cell_collection);$i++) {	
            $column = $objPHPExcel->getActiveSheet()->getCell($cell_collection[$i])->getColumn();
            $row = $objPHPExcel->getActiveSheet()->getCell($cell_collection[$i])->getRow();
            $data_value = $objPHPExcel->getActiveSheet()->getCell($cell_collection[$i])->getValue();
			//header will/should be in row 1 only. of course this can be modified to suit your need.
			
			$arr_data[$row][$column] = $data_value;
			
		}

		
		for($i=1;$i<count($arr_data);$i++){
			foreach($arr_data[$i] as $cell){
				if($i==1){
				echo "<b>header</b> <br>";
				print_r($cell);
				}
				else{
					echo "<b>dataset</b> <br>";
					print_r($cell);
				}
		}
		}
	
	}
}
