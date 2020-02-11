<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MainWebsite extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Audit_model');
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

	public function excel_reader()
	{
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

		echo "<pre>";


		$array = array();
		//extract to a PHP readable array format
		foreach ($objPHPExcel->getWorksheetIterator() as $key => $worksheet) {

			$cell_collection = $worksheet->getCellCollection();
			for ($i = 0; $i < count($cell_collection); $i++) {
				$column = $worksheet->getCell($cell_collection[$i])->getColumn();
				$row = $worksheet->getCell($cell_collection[$i])->getRow();
				$data_value = $worksheet->getCell($cell_collection[$i])->getValue();
				//header will/should be in row 1 only. of course this can be modified to suit your need.
				$arr_data[$key][$row][$column] = $data_value;
			}
		}

		$data = $this->excel_data($arr_data);
		// print_r($data);
		// $process_id='';
		$processes=$this->get_all_process($data[0],'process');
		
		
		foreach($processes as $key=>$value){
			$res=$this->Audit_model->find_process_id('tbl_process','process_name',$value);
			$next_id=0;
			if($res){
				$next_id=$res[0]['process_id'];
			}
			else{
				
				$next_id=$this->Audit_model->getNewIDorNo('p','tbl_process');
				$tbl_data=array('process_name'=>$value,'status'=>0,'process_id'=>$next_id);
				$this->Audit_model->insertData('tbl_process',$tbl_data);		
			}
			////////////////////////////////////////////
			$sub_process=$this->get_scopes($data['0'],$value);
			// print_r($sub_process);
			$scope=$this->get_all_process($sub_process,'scope');
			print_r($scope);
			$tbl_data=array('sub_process_name'=>$value,'process_id'=>$next_id);
			$res=$this->Audit_model->find_process_id('tbl_sub_process','process_id',$next_id);
			if($res){

			}
			else{

			}
			
		}
		// print_r($process_id);
		// if(isset($data)&& !empty($data)){
		// 	for($i=0;$i<count($data);$i++){

		// 		$res=$this->Audit_model->find_process_id('tbl_process','process_id',$data[$i]['process']);

		// 		if($res){
		// 			$res=$this->Audit_model->find_process_id('tbl_sub_process','sub_process_id',$data[$i]['sub_process']);

		// 			if($res){
		// 				$res=$this->Audit_model->find_process_id('tbl_work_steps','work_seteps_id',$data[$i]['work_steps']);

		// 				if($res){
		// 					print_r('Data_required');
		// 					$res=$this->Audit_model->find_process_id('tbl_data_required','id',$data[$i]['Data_required']);

		// 					if($res){
		// 						print_r('risk');
		// 						$res=$this->Audit_model->find_process_id('tbl_risk','risk_id',$data[$i]['risk']);
		// 						print_r("already Enter");

		// 					}
		// 					else{
		// 						$risk=array(
		// 							'risk'=>'risk',
		// 						);
		// 						print_r($risk);
		// 					}
		// 				}
		// 				else{
		// 					$Data_required=array(
		// 						'Data_required'=>'Data_required',
		// 					);
		// 					print_r($Data_required);
		// 				}
		// 			}
		// 			else{
		// 				$sub_process=array(
		// 					'sub_process'=>'sub_process',
		// 				);
		// 				print_r($sub_process);

		// 			}
		// 		}
		// 		else{
		// 			$process=array(
		// 				'process'=>'process',
		// 			);
		// 			print_r($process);
		// 			// die;
		// 			// $res=$this->Audit_model->find_process_id('tbl_sub_process','sub_process_id',$data[$i]['sub_process']);
		// 			// if($res){
		// 			// 	// echo "</br> sub_process </br>";
		// 			// 	// print_r($res);	
		// 			// }
		// 			// else{
		// 			// 	$res=$this->Audit_model->find_process_id('tbl_risk','sub_process_id',$data[$i]['sub_process']);
		// 			// 	if($res){
		// 			// 		// print_r($res);
		// 			// 	}
		// 			// 	else{
		// 			// 		$res=$this->Audit_model->find_process_id('tbl_data_required','sub_process_id',$data[$i]['sub_process']);

		// 			// 		if($res){
		// 			// 			// print_r($res);	
		// 			// 		}
		// 			// 		else{
		// 			// 			$res=$this->Audit_model->find_process_id('tbl_work_steps','sub_process_id',$data[$i]['sub_process']);
		// 			// 			if($res){
		// 			// 				// print_r($res);
		// 			// 			}
		// 			// 			else{
		// 			// 			$process=array(
		// 			// 				'process'=>'process',
		// 			// 				'process_id'=>$data[$i]['process'],
		// 			// 				'sub_process_id'=>$data[$i]['sub_process'],
		// 			// 				'process_name'=>'Name',
		// 			// 				'status'=>0
		// 			// 			);
		// 			// 			$sub_process=array(
		// 			// 				'sub_process'=>'sub_process',
		// 			// 				'process_id'=>$data[$i]['process'],
		// 			// 				'sub_process_id'=>$data[$i]['sub_process'],
		// 			// 				'sub_process_name'=>'sub_name',
		// 			// 				'status'=>0
		// 			// 			);
		// 			// 			$risk=array(
		// 			// 				'risk'=>'risk',
		// 			// 				'sub_process_id'=>$data[$i]['sub_process'],
		// 			// 				'risk_name'=>$data[$i]['risk'],
		// 			// 			);
		// 			// 			$work_steps=array(

		// 			// 				'work_steps'=>'work_steps',
		// 			// 				'sub_process_id'=>$data[$i]['sub_process'],
		// 			// 				'steps_name'=>$data[$i]['work_steps'],
		// 			// 				'status'=>0
		// 			// 			);
		// 			// 			$data_required=array(
		// 			// 				'data_required'=>'data_required',
		// 			// 				'sub_process_id'=>$data[$i]['sub_process'],
		// 			// 				'data_required'=>$data[$i]['Data_required'],
		// 			// 				'status'=>0
		// 			// 			);
		// 						// print_r($process);
		// 						// print_r($sub_process);
		// 						// print_r($risk);
		// 						// print_r($work_steps);
		// 						// print_r($data_required);
		// 		}


		// 	}

		// }
	}

	private function get_all_process($data,$column_name){
		$unique_columns=array_unique(array_column($data,$column_name));
		return $unique_columns;
	}
	private function get_scopes($data,$process){
		$data=$filter = array_filter($data, array(new Filter("process",$process), "filter_callback"));
		return $data;
	}
	private function excel_data($arr_data)
	{
		$final_data = [];
		foreach ($arr_data as $sheetkey => $value) {
			$header = $arr_data[$sheetkey][1];
			$len = count($arr_data[$sheetkey]);
			$data = [];
			for ($i = 2; $i <= $len; $i++) {
				$ob = [];
				foreach ($header as $key => $value) {
					$ob[$header[$key]] =	isset($arr_data[$sheetkey][$i][$key]) ? $arr_data[$sheetkey][$i][$key] : "";
				}
				$data[] = $ob;
			}
			$final_data[$sheetkey] = $data;
		}
		return $final_data;
	}
}
