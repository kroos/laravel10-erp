@extends('layouts.app')

@section('content')

<style>
  /* div {
    border: 1px solid red;
  } */

  a {
    color: #000000;
    text-decoration: none;
  }
</style>

<div class="container">
  @include('humanresources.hrdept.navhr')

  <h4>Appraisal Form</h4>

  <div class="row">&nbsp;</div>

  @foreach ($departments as $department)

  <?php
  $form_versions = DB::table('pivot_dept_appraisals')
    ->where('department_id', $department->id)
    ->whereNotNull('version')
    ->groupBy('department_id')
    ->groupBy('version')
    ->orderBy('version', 'ASC')
    ->get();
  ?>

  <div class="row mb-2" style="background-color: #f0f0f0; font-size: 20px;">
    <div class="col-sm-12 ">
      <a class="btn btn-primary btn-sm" href="{{ route('appraisalform.create', ['id' => $department->id]) }}" role="button">+</a>
      {{ $department->department }}
    </div>
  </div>

  @foreach ($form_versions as $form_version)
  @if ($form_version->version != NULL)
  <div class="row mb-2">
    <div align="right" style="width: 75px;">
      <i class="bi bi-caret-right-fill"></i>
    </div>
    <div class="col-sm-9" style="font-size: 18px;">
      {{ $department->department }} Version {{ $form_version->version }}
    </div>
    <div align="center" style="width: 60px;">
      <a href="{{ route('appraisalform.show', ['appraisalform' => $form_version->id]) }}">
        <button type="submit" class="btn btn-sm btn-outline-secondary">
          <i class="fas fa-file-text" aria-hidden="true"></i>
        </button>
      </a>
    </div>
    <div align="center" style="width: 60px;">
      <a class="fas fa-pencil" href="" role="button"></a>
    </div>
    <div align="center" style="width: 60px;">
      {{ Form::open(['route' => ['appraisalformduplicate.store'], 'method' => 'GET', 'id' => 'form', 'class' => 'form-horizontal', 'autocomplete' => 'off', 'files' => true]) }}
      <input type="hidden" name="id" id="id" value="{{ $form_version->id }}">
      <button type="submit" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-clone" aria-hidden="true"></i>
      </button>
      {{ Form::close() }}
    </div>
    <div align="center" style="width: 60px;">
      <button type="button" class="btn btn-sm btn-outline-secondary appraisal_delete" data-id="{{ $form_version->id }}">
        <i class="fas fa-trash" aria-hidden="true"></i>
      </button>
    </div>
  </div>
  @endif
  @endforeach
  @endforeach

</div>
@endsection

@section('js')
////////////////////////////////////////////////////////////////////////////////////
// DELETE APPRAISAL
$(document).on('click', '.appraisal_delete', function(e){
  var appraisalId = $(this).data('id');
  SwalAppraisalDelete(appraisalId);
  e.preventDefault();
});

function SwalAppraisalDelete(appraisalId){
  swal.fire({
    title: 'Are you sure?',
    text: "It will be deleted permanently!",
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!',
    showLoaderOnConfirm: true,

    preConfirm: function() {
      return new Promise(function(resolve) {
        $.ajax({
          type: 'DELETE',
          url: '{{ url('appraisalform') }}' + '/' + appraisalId,
          data: {
              _token : $('meta[name=csrf-token]').attr('content'),
              id: appraisalId,
          },
          dataType: 'json'
        })
        .done(function(response){
          swal.fire('Deleted!', response.message, response.status)
          .then(function(){
            window.location.reload(true);
          });
        })
        .fail(function(){
          swal.fire('Oops...', 'Something went wrong with ajax !', 'error');
        })
      });
    },
    allowOutsideClick: false
  })
  .then((result) => {
    if (result.dismiss === swal.DismissReason.cancel) {
      swal.fire('Cancelled', 'Your data is safe from delete', 'info')
    }
  });
}
@endsection