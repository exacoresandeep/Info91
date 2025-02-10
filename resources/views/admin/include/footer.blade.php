

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
   
  <!-- Main Footer -->
 <footer class="main-footer">
    <strong>Copyright &copy; 2024-2025 <a href="">info91</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 1.0 Developed by Exacore
    </div>
  </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->
<!-- jQuery -->
<script src="{{asset('panel/plugins/jquery/jquery.min.js')}}"></script>
<!-- Bootstrap -->
<script src="{{asset('panel/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<!-- overlayScrollbars -->
<script src="{{asset('panel/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js')}}"></script>
<!-- AdminLTE App -->
 <!-- DataTables  & Plugins -->
<script src="{{asset('panel/plugins/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('panel/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
<script src="{{asset('panel/plugins/datatables-responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('panel/plugins/datatables-responsive/js/responsive.bootstrap4.min.js')}}"></script>
<script src="{{asset('panel/plugins/datatables-buttons/js/dataTables.buttons.min.js')}}"></script>
<script src="{{asset('panel/plugins/datatables-buttons/js/buttons.bootstrap4.min.js')}}"></script>
<script src="{{asset('panel/plugins/jszip/jszip.min.js')}}"></script>
<script src="{{asset('panel/plugins/pdfmake/pdfmake.min.js')}}"></script>
<script src="{{asset('panel/plugins/pdfmake/vfs_fonts.js')}}"></script>
<script src="{{asset('panel/plugins/datatables-buttons/js/buttons.html5.min.js')}}"></script>
<script src="{{asset('panel/plugins/datatables-buttons/js/buttons.print.min.js')}}"></script>
<script src="{{asset('panel/plugins/datatables-buttons/js/buttons.colVis.min.js')}}"></script>
<!-- AdminLTE App -->
<script src="{{asset('panel/dist/js/adminlte.js')}}"></script>

<!-- PAGE PLUGINS -->
<!-- jQuery Mapael -->
<script src="{{asset('panel/plugins/jquery-mousewheel/jquery.mousewheel.js')}}"></script>
<script src="{{asset('panel/plugins/raphael/raphael.min.js')}}"></script>
<script src="{{asset('panel/plugins/jquery-mapael/jquery.mapael.min.js')}}"></script>
<script src="{{asset('panel/plugins/jquery-mapael/maps/usa_states.min.js')}}"></script>
<!-- ChartJS -->
<script src="{{asset('panel/plugins/chart.js/Chart.min.js')}}"></script>

<!-- AdminLTE for demo purposes -->
<script src="{{asset('panel/dist/js/demo.js')}}"></script>

<script>
function getCookie(name) {
    let match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? decodeURIComponent(match[2]) : null;
}

function loadContent(link) {
    $.ajax({
        url: '/load-content/' + link,
        method: 'GET',
        success: function (response) {
            $("#content-area").html(response);
        },
        error: function (xhr, status, error) {
            $("#content-area").html("Error: " + xhr.status + " " + xhr.statusText);
        }
    });
}

$(document).ready(function () {
    var selectedLink = getCookie("selectedLink");
    if (selectedLink) {
        $('.nav-link').removeClass('active');
        var linkElement = $('.nav-link[href="' + selectedLink + '"]');
        if (linkElement.length) {
            linkElement.addClass('active');
            var parentMenu = linkElement.closest('.nav.nav-treeview');
            if (parentMenu.length) {
                var parentMenuLi = parentMenu.closest('.nav-item');
                parentMenuLi.addClass('menu-open menu-is-opening');
                parentMenu.find('> a').addClass('active');
            }
        }
        loadContent(selectedLink);
    } else {
        loadContent('dashboard');
    }

    $(".nav-link").on("click", function (event) {
        event.preventDefault();
        $('.nav-link').removeClass('active');
        var link = $(this).attr("href");
        if (link !== "#") {
            document.cookie = "selectedLink=" + encodeURIComponent(link) + "; path=/";
            $(this).addClass('active');
            loadContent(link);
        }
    });

    $('.nav-item.dropdown').on('click', function (event) {
        event.preventDefault();
        $('.nav-item.dropdown').removeClass('active');
        $(this).addClass('active');
        $('.dropdown-menu').slideUp();
        $(this).find('.dropdown-menu').slideToggle();
    });

    $(document).on('click', function (event) {
        if (!$(event.target).closest('.nav-item.dropdown').length) {
            $('.dropdown-menu').slideUp();
            $('.nav-item.dropdown').removeClass('active');
        }
    });
});

</script>
</body>
</html>
