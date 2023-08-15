<style>
li.dropdown {
		position: top;
	}
</style>
<ul class="nav justify-content-center">
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Staff</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('staff.index') }}"><i class="bi bi-person"></i> Staff List</a></li>
			<li><a class="dropdown-item btn btn-sm btn-outline-secondary" href="{{ route('staff.create') }}"><i class="bi bi-person-add"></i> Add Staff</a></li>
			<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li>
		</ul>
	</li>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Attendance</a>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item" href="{{ route('attendance.index') }}">Attendance List</a></li>
			<li><a class="dropdown-item" href="#">Another action</a></li>
			<li><a class="dropdown-item" href="#">Something else here</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#">Separated link</a></li>
		</ul>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="#">Link</a>
	</li>
	<li class="nav-item">
		<a class="nav-link disabled" aria-disabled="true">Disabled</a>
	</li>
</ul>









///////////////////////////////////////////////////////////////////
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
<div class="container-fluid">
<a class="navbar-brand" href="#">Brand</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main_nav"  aria-expanded="false" aria-label="Toggle navigation">
<span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse" id="main_nav">
  <ul class="navbar-nav">
    <li class="nav-item active"> <a class="nav-link" href="#">Home </a> </li>
    <li class="nav-item"><a class="nav-link" href="#"> About </a></li>
    <li class="nav-item dropdown" id="myDropdown">
      <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">  Treeview menu  </a>
      <ul class="dropdown-menu">
        <li> <a class="dropdown-item" href="#"> Dropdown item 1 </a></li>
        <li> <a class="dropdown-item" href="#"> Dropdown item 2 &raquo; </a>
          <ul class="submenu dropdown-menu">
            <li><a class="dropdown-item" href="#">Submenu item 1</a></li>
            <li><a class="dropdown-item" href="#">Submenu item 2</a></li>
            <li><a class="dropdown-item" href="#">Submenu item 3 &raquo; </a>
              <ul class="submenu dropdown-menu">
                <li><a class="dropdown-item" href="#">Multi level 1</a></li>
                <li><a class="dropdown-item" href="#">Multi level 2</a></li>
              </ul>
            </li>
            <li><a class="dropdown-item" href="#">Submenu item 4</a></li>
            <li><a class="dropdown-item" href="#">Submenu item 5</a></li>
          </ul>
        </li>
        <li><a class="dropdown-item" href="#"> Dropdown item 3 </a></li>
        <li><a class="dropdown-item" href="#"> Dropdown item 4 </a></li>
      </ul>
    </li>
  </ul>
</div>
<!-- navbar-collapse.// -->
</div>
<!-- container-fluid.// -->
</nav>
///////////////////////////////////////////////////////////////////
document.addEventListener("DOMContentLoaded", function(){
// make it as accordion for smaller screens
if (window.innerWidth < 992) {

  // close all inner dropdowns when parent is closed
  document.querySelectorAll('.navbar .dropdown').forEach(function(everydropdown){
    everydropdown.addEventListener('hidden.bs.dropdown', function () {
      // after dropdown is hidden, then find all submenus
        this.querySelectorAll('.submenu').forEach(function(everysubmenu){
          // hide every submenu as well
          everysubmenu.style.display = 'none';
        });
    })
  });

  document.querySelectorAll('.dropdown-menu a').forEach(function(element){
    element.addEventListener('click', function (e) {
        let nextEl = this.nextElementSibling;
        if(nextEl && nextEl.classList.contains('submenu')) {
          // prevent opening link if link needs to open dropdown
          e.preventDefault();
          if(nextEl.style.display == 'block'){
            nextEl.style.display = 'none';
          } else {
            nextEl.style.display = 'block';
          }

        }
    });
  })
}
// end if innerWidth
});
// DOMContentLoaded  end
///////////////////////////////////////////////////////////////////

/* ============ desktop view ============ */
@media all and (min-width: 992px) {
	.dropdown-menu li{ position: relative; 	}
	.nav-item .submenu{
		display: none;
		position: absolute;
		left:100%; top:-7px;
	}
	.nav-item .submenu-left{
		right:100%; left:auto;
	}
	.dropdown-menu > li:hover{ background-color: #f1f1f1 }
	.dropdown-menu > li:hover > .submenu{ display: block; }
}
/* ============ desktop view .end// ============ */

/* ============ small devices ============ */
@media (max-width: 991px) {
  .dropdown-menu .dropdown-menu{
      margin-left:0.7rem; margin-right:0.7rem; margin-bottom: .5rem;
  }
}
/* ============ small devices .end// ============ */
