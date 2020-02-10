<form action="<?php echo base_url("upload/");?>" method="POST" enctype="multipart/form-data">
<h2> Upload Excel File </h2>
<input type="file" name="sample_file" id="sample_file"> <br> <br>
<input type="submit" value="Upload">
<?php
if($this->session->flashdata("error")){
echo $this->session->flashdata("error");

}
?>
</form>