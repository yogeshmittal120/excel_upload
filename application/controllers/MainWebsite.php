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
		$processes=$this->get_all_unique($data[0],'process');
		
		
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
			$sub_process=$this->get_data_by_filter($data['0'],$value,'process');
			$scope=$this->get_all_unique($sub_process,'scope');
			foreach($scope as $key=>$value){
				$condition=array('sub_process_name'=>$value,'process_id'=>$next_id);
				$next_sub_process_id=0;
				$res=$this->Audit_model->select_table_Where_data('tbl_sub_process',$condition);
				if($res){
					$next_sub_process_id=$res[0]['sub_process_id'];
				}
				else{
					$next_sub_process_id=$this->Audit_model->getNewIDorNo('sp','tbl_sub_process');
					$tbl_data=array('sub_process_name'=>$value,'status'=>0,'process_id'=>$next_id,'sub_process_id'=>$next_sub_process_id);
					$this->Audit_model->insertData('tbl_sub_process',$tbl_data);
				}
				//////////////////////////////////
				$data_required=$this->get_data_by_filter($data['0'],$value,'scope');
				$Data_required=$this->get_all_unique($data_required,'Data_required');
				
				foreach($Data_required as $key=>$value){
					$condition=array('data_required'=>$value,'sub_process_id'=>$next_sub_process_id);
					$next_Data_required_id=0;
					$res=$this->Audit_model->select_table_Where_data('tbl_data_required',$condition);
					if($res){
						$next_Data_required_id=$res[0]['id'];
					}
					else{
						// $next_sub_process_id=$this->Audit_model->getNewIDorNo('sp','tbl_data_required');
						$tbl_data=array('data_required'=>$value,'status'=>0,'sub_process_id'=>$next_sub_process_id);
						$this->Audit_model->insertData('tbl_data_required',$tbl_data);
					}

				}
				////////////////////////////////
				// $stepName=$this->get_data_by_filter($data['0'],$value,'scope');
				$step_name=$this->get_all_unique($data_required,'step_name');
				print_r($step_name);
				foreach($step_name as $key=>$value){
					$condition=array('steps_name'=>$value,'sub_process_id'=>$next_sub_process_id);
					$next_step_name_id=0;
					$res=$this->Audit_model->select_table_Where_data('tbl_work_steps',$condition);
					if($res){
						$next_step_name_id=$res[0]['work_seteps_id'];
					}
					else{
						// $next_sub_process_id=$this->Audit_model->getNewIDorNo('sp','tbl_data_required');
						$tbl_data=array('steps_name'=>$value,'status'=>0,'sub_process_id'=>$next_sub_process_id);
						$this->Audit_model->insertData('tbl_work_steps',$tbl_data);
					}

				}
				////////////////////////////////
				// $stepName=$this->get_data_by_filter($data['0'],$value,'scope');
				$risk_name=$this->get_all_unique($data_required,'step_name');
				print_r($risk_name);
				foreach($risk_name as $key=>$value){
					$condition=array('risk_name'=>$value,'sub_process_id'=>$next_sub_process_id);
					$next_step_name_id=0;
					$res=$this->Audit_model->select_table_Where_data('tbl_risk',$condition);
					if($res){
						$next_step_name_id=$res[0]['risk_id'];
					}
					else{
						// $next_sub_process_id=$this->Audit_model->getNewIDorNo('sp','tbl_data_required');
						$tbl_data=array('risk_name'=>$value,'sub_process_id'=>$next_sub_process_id);
						$this->Audit_model->insertData('tbl_risk',$tbl_data);
					}

				}


			}
			
		}
		
	}

	private function get_all_unique($data,$column_name){
		$unique_columns=array_unique(array_column($data,$column_name));
		return $unique_columns;
	}
	private function get_data_by_filter($data,$process,$col_name){
		$data=$filter = array_filter($data, array(new Filter($col_name,$process), "filter_callback"));
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
