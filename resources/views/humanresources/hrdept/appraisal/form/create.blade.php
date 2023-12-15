@extends('layouts.app')

@section('content')

<style>
	/* div {
		border: 1px solid red;
	} */
</style>


<div class="container">
	@include('humanresources.hrdept.navhr')

	<h4>Appraisal Form : {{ $department->department }}</h4>

	<textarea id="basic-example">

	</textarea>

</div>
@endsection

@section('js')
tinymce.init({
  selector: 'textarea#basic-example',
  height: 500,
  plugins: [
    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
    'insertdatetime', 'media', 'table', 'help', 'wordcount'
  ],
  toolbar: 'undo redo | blocks | ' +
  'bold italic backcolor | alignleft aligncenter ' +
  'alignright alignjustify | bullist numlist outdent indent | ' +
  'removeformat | help',
  content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
});
@endsection