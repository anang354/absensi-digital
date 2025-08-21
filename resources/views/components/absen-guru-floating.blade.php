{{-- @if (auth()->user()->level !== 'siswa')
    <a
    href="{{ url('/admin/guru-scan') }}"
    class="
        fixed z-50
        bg-primary-600 hover:bg-primary-700
        text-white p-4 rounded-full shadow-lg
        flex items-center justify-center
        transition duration-150 ease-in-out
        filament-button filament-button-size-lg filament-button-outlined
    "
    style="bottom: 20px; right: 20px; --c-400:var(--success-400);--c-500:var(--success-500);--c-600:var(--success-600);"
    title="Absen Guru" 
    <x-heroicon-o-camera class="h-4 w-4" />
</a>
    <a
    href="{{ url('/admin') }}"
    class="
        fixed z-50
        text-white p-4 rounded-full shadow-lg
        flex items-center justify-center
        transition duration-150 ease-in-out
        filament-button filament-button-size-lg filament-button-outlined
    "
    style="background: oklch(50% .134 242.749); bottom: 80px; right: 20px; --c-400:var(--success-400);--c-500:var(--success-500);--c-600:var(--success-600);"
    title="Absen Siswa" 
>

    <x-heroicon-o-qr-code class="h-4 w-4" />
</a>
@else
<div></div>
@endif --}}

@if(auth()->user())
@if(auth()->user()->level === 'admin' || auth()->user()->level === 'guru' || auth()->user()->level === 'superadmin')
<div class="radial active">
  <a class="triggerButton" href="">
     <x-heroicon-o-camera class="h-4 w-4 faOpen" />
     <x-heroicon-o-qr-code class="h-4 w-4 faOpen" />
     <x-heroicon-o-map-pin class="h-4 w-4 faOpen" />
     <x-heroicon-o-x-mark class="h-6 w-6 faClose" />
  </a>
  <ul class="radialMenu active">

    <li id="fa-1" class="radialItem">
      <a href="{{ url('/admin/guru-scan') }}">
        <x-heroicon-o-camera class="h-6 w-6" />
        <span>Absen Guru</span>
      </a>
    </li>
    <li id="fa-2" class="radialItem">
      <a href="{{ url('/admin/scan-siswa') }}">
        <x-heroicon-o-qr-code class="h-6 w-6" />
        <span>Absen Siswa</span>
      </a>
    </li>
    <li id="fa-3" class="radialItem">
      <a href="{{ url('/admin/absen-siswas') }}">
        <x-heroicon-o-document-text class="h-6 w-6" />
        <span>Rekap Absen</span>
      </a>
    </li>
  </ul>
</div>


<script>
    // Get all menu from document
document.querySelectorAll('.triggerButton').forEach(OpenMenu);

// Menu Open and Close function
function OpenMenu(active) {
    if(active.classList.contains('triggerButton') === true){
        active.addEventListener('click', function (e) {
            e.preventDefault();        
    
            if (this.nextElementSibling.classList.contains('active') === true) {
                // Close the clicked dropdown
                this.parentElement.classList.remove('active');
                this.nextElementSibling.classList.remove('active');
    
            } else {
                // Close the opend dropdown
                closeMenu();
                // add the open and active class(Opening the DropDown)
                this.parentElement.classList.add('active');
                this.nextElementSibling.classList.add('active');
            }
        });
    }
};

// Listen to the doc click
window.addEventListener('click', function (e) {

    // Close the menu if click happen outside menu
    if (e.target.closest('.radial') === null) {
        // Close the opend dropdown
        closeMenu();
    }

});


// Close the openend Menu
function closeMenu() { 
    // remove the open and active class from other opened Moenu (Closing the opend Menu)
    document.querySelectorAll('.radial').forEach(function (container) { 
        container.classList.remove('active')
    });

    document.querySelectorAll('.radialMenu').forEach(function (menu) { 
        menu.classList.remove('active');
    });
}
closeMenu();
</script>
@endif
@endif