<?php
namespace App\Helpers;

class FormHTML
{
	public function __construct()
	{
	}

	public static function fselectyesno($id='', $label='', $input_type='')
	{
		$text = '

								<div class="form-group row">
									<label for="sta" class="col-4 col-form-label text-right">'.$label.' : </label>
									<input type="hidden" name="form['.$id.'][label]" value="'.$label.'" />
									<input type="hidden" name="form['.$id.'][input_type]" value="'.$input_type.'" />
									<div class="col-6">
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'0" name="form['.$id.'][input]" type="radio" value="Yes" >
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'0">Yes</label>
											</div>
										</div>
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'1" name="form['.$id.'][input]" type="radio" value="No">
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'1">No</label>
											</div>
										</div>
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'2" name="form['.$id.'][input]" type="radio" value="Not Applicable">
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'2">Not Applicable</label>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group row ">
									<label for="'.$id.'" class="col-4 col-form-label text-right">Remarks : </label>
									<div class="col-6">
										<input name="form['.$id.'][remarks]" class="form-control form-control-sm" id="'.$id.'" placeholder="Remarks" autocomplete="off" type="text">
									</div>
								</div>
		';
		return $text;
	}

	public static function fselectpassfail($id='', $label='', $input_type='')
	{
		$text = '
								<div class="form-group row ">
									<label for="sta" class="col-4 col-form-label text-right">'.$label.' : </label>
									<input type="hidden" name="form['.$id.'][label]" value="'.$label.'" />
									<input type="hidden" name="form['.$id.'][input_type]" value="'.$input_type.'" />
									<div class="col-6">
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'0" name="form['.$id.'][input]" type="radio" value="Pass">
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'0">Pass</label>
											</div>
										</div>
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'1" name="form['.$id.'][input]" type="radio" value="Fail">
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'1">Fail</label>
											</div>
										</div>
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'2" name="form['.$id.'][input]" type="radio" value="Not Applicable">
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'2">Not Applicable</label>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group row ">
									<label for="'.$id.'" class="col-4 col-form-label text-right">Remarks : </label>
									<div class="col-6">
										<input name="form['.$id.'][remarks]" class="form-control form-control-sm" id="'.$id.'" placeholder="Remarks" autocomplete="off" type="text">
									</div>
								</div>
		';
		return $text;
	}

	public static function fselectgoodbad($id='', $label='', $input_type='')
	{
		$text = '
								<div class="form-group row ">
									<label for="sta" class="col-4 col-form-label text-right">'.$label.' : </label>
									<input type="hidden" name="form['.$id.'][label]" value="'.$label.'" />
									<input type="hidden" name="form['.$id.'][input_type]" value="'.$input_type.'" />
									<div class="col-6">
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'0" name="form['.$id.'][input]" type="radio" value="Good" >
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'0">Good</label>
											</div>
										</div>
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'1" name="form['.$id.'][input]" type="radio" value="Bad" >
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'1">Bad</label>
											</div>
										</div>
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'2" name="form['.$id.'][input]" type="radio" value="Not Applicable" >
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'2">Not Applicable</label>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group row ">
									<label for="'.$id.'" class="col-4 col-form-label text-right">Remarks : </label>
									<div class="col-6">
										<input name="form['.$id.'][remarks]" class="form-control form-control-sm" id="'.$id.'" placeholder="Remarks" autocomplete="off" type="text">
									</div>
								</div>
		';
		return $text;
	}

	public static function fselectcompliantnoncompliant($id='', $label='', $input_type='')
	{
		$text = '
								<div class="form-group row ">
									<label for="sta" class="col-4 col-form-label text-right">'.$label.' : </label>
									<input type="hidden" name="form['.$id.'][label]" value="'.$label.'" />
									<input type="hidden" name="form['.$id.'][input_type]" value="'.$input_type.'" />
									<div class="col-6">
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'0" name="form['.$id.'][input]" type="radio" value="Compliant">
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'0">Compliant</label>
											</div>
										</div>
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'1" name="form['.$id.'][input]" type="radio" value="Non Compliant">
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'1">Non Compliant</label>
											</div>
										</div>
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'2" name="form['.$id.'][input]" type="radio" value="Not Applicable">
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'2">Not Applicable</label>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group row ">
									<label for="'.$id.'" class="col-4 col-form-label text-right">Remarks : </label>
									<div class="col-6">
										<input name="form['.$id.'][remarks]" class="form-control form-control-sm" id="'.$id.'" placeholder="Remarks" autocomplete="off" type="text">
									</div>
								</div>
		';
		return $text;
	}

	public static function ftext($id='', $label='', $input_type='')
	{
		$text = '
								<div class="form-group row ">
									<label for="'.$id.'" class="col-4 col-form-label text-right">'.$label.' : </label>
									<div class="col-6">
										<input name="form['.$id.'][input]" value="" class="form-control form-control-sm" id="'.$id.'" placeholder="'.$label.'" autocomplete="off" type="text">
										<input type="hidden" name="form['.$id.'][label]" value="'.$label.'" />
										<input type="hidden" name="form['.$id.'][input_type]" value="'.$input_type.'" />
									</div>
								</div>
								<div class="form-group row ">
									<label for="'.$id.'" class="col-4 col-form-label text-right">Remarks : </label>
									<div class="col-6">
										<input name="form['.$id.'][remarks]" class="form-control form-control-sm" id="'.$id.'" placeholder="Remarks" autocomplete="off" type="text">
									</div>
								</div>
		';
		return $text;
	}

	public static function ftextarea($id='', $label='', $input_type='')
	{
		$text = '
								<div class="form-group row ">
									<label for="'.$id.'" class="col-4 col-form-label text-right">'.$label.' : </label>
									<div class="col-6">
										<textarea name="form['.$id.'][input]" class="form-control form-control-sm" id="'.$id.'" placeholder="'.$label.'" autocomplete="off"></textarea>
										<input type="hidden" name="form['.$id.'][label]" value="'.$label.'" />
										<input type="hidden" name="form['.$id.'][input_type]" value="'.$input_type.'" />
									</div>
								</div>
								<div class="form-group row ">
									<label for="'.$id.'" class="col-4 col-form-label text-right">Remarks : </label>
									<div class="col-6">
										<input name="form['.$id.'][remarks]" class="form-control form-control-sm" id="'.$id.'" placeholder="Remarks" autocomplete="off" type="text">
									</div>
								</div>
		';
		return $text;
	}

	public static function fuploadimage($id='', $label='', $input_type='')
	{
		$text = '
								<div class="delete_input">
								<div class="form-group row remove_form">
									<label for="'.$id.'" class="col-4 col-form-label text-right"><span class="text-danger"><i class="fas fa-trash remove_attd" aria-hidden="true" ></i></span> '.$label.' : </label>
									<div class="col-6">
										<input type="file" name="image['.$id.'][input]" class="custom-file-input form-input-sm" id="'.$id.'" >
										<label class="custom-file-label" for="'.$id.'">Upload</label>
										<input type="hidden" name="image['.$id.'][label]" value="'.$label.'" />
										<input type="hidden" name="image['.$id.'][id]" value="" />
										<input type="hidden" name="image['.$id.'][input_type]" value="'.$input_type.'" />
									</div>
								</div>
								<div class="form-group row custom-file mb-3">
									<label for="'.$id.'" class="col-4 col-form-label text-right">Remarks : </label>
									<div class="col-6">
										<input name="image['.$id.'][remarks]" class="form-control form-control-sm" id="'.$id.'" placeholder="Remarks" autocomplete="off" type="text">
									</div>
								</div>
								</div>
								<script type="text/javascript">
									jQuery.noConflict ();
									(function($){
										$(document).ready(function(){
											$(".remove_form").on("click",".remove_attd", function(e){
												e.preventDefault();
												var $row = $(this).parent().parent().parent().parent();
												// $row.css({"background":"red"});
												$row.remove();
												console.log(1);
											})
										});
									})(jQuery);
								</script>
		';
		return $text;
	}

	public static function fuploaddoc($id='', $label='', $input_type='')
	{
		$text = '
								<div class="form-group row ">
									<label for="'.$id.'" class="col-4 col-form-label text-right">'.$label.' : </label>
									<div class="col-6">
										<input type="file" name="doc['.$id.'][input]" class="custom-file-input form-input-sm" id="'.$id.'" >
										<label class="custom-file-label" for="'.$id.'">Upload</label>
										<input type="hidden" name="doc['.$id.'][label]" value="'.$label.'" />
										<input type="hidden" name="doc['.$id.'][id]" value="" />
										<input type="hidden" name="doc['.$id.'][input_type]" value="'.$input_type.'" />
									</div>
								</div>
								<div class="form-group row custom-file mb-3">
									<label for="'.$id.'" class="col-4 col-form-label text-right">Remarks : </label>
									<div class="col-6">
										<input name="doc['.$id.'][remarks]" class="form-control form-control-sm" id="'.$id.'" placeholder="Remarks" autocomplete="off" type="text">
									</div>
								</div>
		';
		return $text;
	}
































































































}