<?php
namespace App\Helpers;

class UpdateFormHTML
{
	public function __construct()
	{
	}

	public static function fselectyesno($id='', $label='', $input_type='', $input='', $remarks='')
	{
		$text = '
								<div class="form-group row">
									<label for="sta" class="col-4 col-form-label text-right">'.$label.' : </label>
									<input type="hidden" name="form['.$id.'][label]" value="'.$label.'" />
									<input type="hidden" name="form['.$id.'][id]" value="'.$id.'" />
									<input type="hidden" name="form['.$id.'][input_type]" value="'.$input_type.'" />
									<div class="col-6">
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'0" name="form['.$id.'][input]" type="radio" value="Yes" '.(($input=='Yes')?'checked':NULL).'>
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'0">Yes</label>
											</div>
										</div>
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'1" name="form['.$id.'][input]" type="radio" value="No" '.(($input=='No')?'checked':NULL).'>
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'1">No</label>
											</div>
										</div>
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'2" name="form['.$id.'][input]" type="radio" value="Not Applicable" '.(($input=='Not Applicable')?'checked':NULL).'>
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'2">Not Applicable</label>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group row ">
									<label for="'.$id.'" class="col-4 col-form-label text-right">Remarks : </label>
									<div class="col-6">
										<input name="form['.$id.'][remarks]" value="'.$remarks.'" class="form-control form-control-sm" id="'.$id.'" placeholder="Remarks" autocomplete="off" type="text">
									</div>
								</div>
		';
		return $text;
	}

	public static function fselectpassfail($id='', $label='', $input_type='', $input='', $remarks='')
	{
		$text = '
								<div class="form-group row ">
									<label for="sta" class="col-4 col-form-label text-right">'.$label.' : </label>
									<input type="hidden" name="form['.$id.'][label]" value="'.$label.'" />
									<input type="hidden" name="form['.$id.'][id]" value="'.$id.'" />
									<input type="hidden" name="form['.$id.'][input_type]" value="'.$input_type.'" />
									<div class="col-6">
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'0" name="form['.$id.'][input]" type="radio" value="Pass" '.(($input=='Pass')?'checked':NULL).'>
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'0">Pass</label>
											</div>
										</div>
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'1" name="form['.$id.'][input]" type="radio" value="Fail" '.(($input=='Fail')?'checked':NULL).'>
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'1">Fail</label>
											</div>
										</div>
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'2" name="form['.$id.'][input]" type="radio" value="Not Applicable" '.(($input=='Not Applicable')?'checked':NULL).'>
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'2">Not Applicable</label>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group row ">
									<label for="'.$id.'" class="col-4 col-form-label text-right">Remarks : </label>
									<div class="col-6">
										<input name="form['.$id.'][remarks]" value="'.$remarks.'" class="form-control form-control-sm" id="'.$id.'" placeholder="Remarks" autocomplete="off" type="text">
									</div>
								</div>
		';
		return $text;
	}

	public static function fselectgoodbad($id='', $label='', $input_type='', $input='', $remarks='')
	{
		$text = '
								<div class="form-group row ">
									<label for="sta" class="col-4 col-form-label text-right">'.$label.' : </label>
									<input type="hidden" name="form['.$id.'][label]" value="'.$label.'" />
									<input type="hidden" name="form['.$id.'][id]" value="'.$id.'" />
									<input type="hidden" name="form['.$id.'][input_type]" value="'.$input_type.'" />
									<div class="col-6">
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'0" name="form['.$id.'][input]" type="radio" value="Good" '.(($input=='Good')?'checked':NULL).'>
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'0">Good</label>
											</div>
										</div>
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'1" name="form['.$id.'][input]" type="radio" value="Bad" '.(($input=='Bad')?'checked':NULL).'>
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'1">Bad</label>
											</div>
										</div>
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'2" name="form['.$id.'][input]" type="radio" value="Not Applicable" '.(($input=='Not Applicable')?'checked':NULL).'>
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'2">Not Applicable</label>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group row ">
									<label for="'.$id.'" class="col-4 col-form-label text-right">Remarks : </label>
									<div class="col-6">
										<input name="form['.$id.'][remarks]" value="'.$remarks.'" class="form-control form-control-sm" id="'.$id.'" placeholder="Remarks" autocomplete="off" type="text">
									</div>
								</div>
		';
		return $text;
	}

	public static function fselectcompliantnoncompliant($id='', $label='', $input_type='', $input='', $remarks='')
	{
		$text = '
								<div class="form-group row ">
									<label for="sta" class="col-4 col-form-label text-right">'.$label.' : </label>
									<input type="hidden" name="form['.$id.'][label]" value="'.$label.'" />
									<input type="hidden" name="form['.$id.'][input_type]" value="'.$input_type.'" />
									<div class="col-6">
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'0" name="form['.$id.'][input]" type="radio" value="Compliant" '.(($input=='Compliant')?'checked':NULL).'>
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'0">Compliant</label>
											</div>
										</div>
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'1" name="form['.$id.'][input]" type="radio" value="Non Compliant" '.(($input=='Non Compliant')?'checked':NULL).'>
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'1">Non Compliant</label>
											</div>
										</div>
										<div class="pretty p-switch">
											<input class="form-check-input" id="'.$id.'2" name="form['.$id.'][input]" type="radio" value="Not Applicable" '.(($input=='Not Applicable')?'checked':NULL).'>
											<div class="state p-success">
												<label class="form-check-label" for="'.$id.'2">Not Applicable</label>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group row ">
									<label for="'.$id.'" class="col-4 col-form-label text-right">Remarks : </label>
									<div class="col-6">
										<input name="form['.$id.'][remarks]" value="'.$remarks.'" class="form-control form-control-sm" id="'.$id.'" placeholder="Remarks" autocomplete="off" type="text">
									</div>
								</div>
		';
		return $text;
	}

	public static function ftext($id='', $label='', $input_type='', $input='', $remarks='')
	{
		$text = '
								<div class="form-group row ">
									<label for="'.$id.'" class="col-4 col-form-label text-right">'.$label.' : </label>
									<div class="col-6">
										<input name="form['.$id.'][input]" value="'.$input.'" class="form-control form-control-sm" id="'.$id.'" placeholder="'.$label.'" autocomplete="off" type="text">
										<input type="hidden" name="form['.$id.'][id]" value="'.$id.'" />
										<input type="hidden" name="form['.$id.'][label]" value="'.$label.'" />
										<input type="hidden" name="form['.$id.'][input_type]" value="'.$input_type.'" />
									</div>
								</div>
								<div class="form-group row ">
									<label for="'.$id.'" class="col-4 col-form-label text-right">Remarks : </label>
									<div class="col-6">
										<input name="form['.$id.'][remarks]" value="'.$remarks.'" class="form-control form-control-sm" id="'.$id.'" placeholder="Remarks" autocomplete="off" type="text">
									</div>
								</div>
		';
		return $text;
	}

	public static function ftextarea($id='', $label='', $input_type='', $input='', $remarks='')
	{
		$text = '
								<div class="form-group row ">
									<label for="'.$id.'" class="col-4 col-form-label text-right">'.$label.' : </label>
									<div class="col-6">
										<textarea name="form['.$id.'][input]" class="form-control form-control-sm" id="'.$id.'" placeholder="'.$label.'" autocomplete="off">'.$input.'</textarea>
										<input type="hidden" name="form['.$id.'][id]" value="'.$id.'" />
										<input type="hidden" name="form['.$id.'][label]" value="'.$label.'" />
										<input type="hidden" name="form['.$id.'][input_type]" value="'.$input_type.'" />
									</div>
								</div>
								<div class="form-group row ">
									<label for="'.$id.'" class="col-4 col-form-label text-right">Remarks : </label>
									<div class="col-6">
										<input name="form['.$id.'][remarks]" value="'.$remarks.'" class="form-control form-control-sm" id="'.$id.'" placeholder="Remarks" autocomplete="off" type="text">
									</div>
								</div>
		';
		return $text;
	}

	public static function fuploadimage($id='', $label='', $input_type='', $input='', $remarks='')
	{
		$text = '
								<div class="form-group row ">
									<label for="'.$id.'" class="col-4 col-form-label text-right">'.$label.' : </label>
									<div class="col-6">
										<input type="file" name="image['.$id.'][input]" class="custom-file-input form-input-sm" id="'.$id.'" >
										<label class="custom-file-label" for="'.$id.'">Upload</label>
										<input type="hidden" name="image['.$id.'][label]" value="'.$label.'" />
										<input type="hidden" name="image['.$id.'][id]" value="'.$id.'" />
										<input type="hidden" name="image['.$id.'][input_type]" value="'.$input_type.'" />
									</div>
								</div>
								<div class="form-group row custom-file mb-3">
									<label for="'.$id.'" class="col-4 col-form-label text-right">Remarks : </label>
									<div class="col-6">
										<input name="image['.$id.'][remarks]" value="'.$remarks.'" class="form-control form-control-sm" id="'.$id.'" placeholder="Remarks" autocomplete="off" type="text">
									</div>
								</div>
		';
		return $text;
	}

	public static function fuploaddoc($id='', $label='', $input_type='', $input='', $remarks='')
	{
		$text = '
								<div class="form-group row ">
									<label for="'.$id.'" class="col-4 col-form-label text-right">'.$label.' : </label>
									<div class="col-6">
										<input type="file" name="doc['.$id.'][input]" class="custom-file-input form-input-sm" id="'.$id.'" >
										<label class="custom-file-label" for="'.$id.'">Upload</label>
										<input type="hidden" name="doc['.$id.'][label]" value="'.$label.'" />
										<input type="hidden" name="doc['.$id.'][id]" value="'.$id.'" />
										<input type="hidden" name="doc['.$id.'][input_type]" value="'.$input_type.'" />
									</div>
								</div>
								<div class="form-group row custom-file mb-3">
									<label for="'.$id.'" class="col-4 col-form-label text-right">Remarks : </label>
									<div class="col-6">
										<input name="doc['.$id.'][remarks]" value="'.$remarks.'" class="form-control form-control-sm" id="'.$id.'" placeholder="Remarks" autocomplete="off" type="text">
									</div>
								</div>
		';
		return $text;
	}
































































































}