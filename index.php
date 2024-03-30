<?php

$main_content_width = "25%";

echo "
<!DOCTYPE HTML>
<head>
<link rel='stylesheet' href='./style.css'>

<style>

a {
	color: var(--main-font-color);
}

.form_input_label {
    float: left;
    margin-top: 1em;
    margin-left: 1px;
    font-size: 1.25em;
}

#storage_description {
    min-height: 200px;
    font-size: 1.2em;
    max-width: 100%;
    min-width: 100%;
}

@media (pointer: coarse) {
    #main_form {
        width: 100% !important;
    }

    .button, .button_danger {
        max-width: 140px;
    }

    .qr_code_button {
        max-width: 120px !important;
    }
}

.button_qr_code {
    width: 45px;
    height: auto;
    font-size: 2em;
    background-color: transparent;
    color: var(--main-font-color);
    border-radius: 8px;
    cursor: pointer !important;
    transition: 0.3s;
}

#qr_code_container {
    text-align: center;
    background-color: white;
    margin: auto;
    padding-top: 45px;
    padding-bottom: 45px;
    border-right: 1px black solid;
    border-bottom: 1px black solid;
    width: 270px;
    height: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    word-wrap: break-word;
}

#qr_code_container img {
    margin: auto;
}

#qr_code_title {
    color: #000000;
    line-height: 1.3em;
    font-size: 15pt;
    float: left;
    padding-top: 5px;
    width: 100%;
    text-align: center;
}

.open_button_cell {
    cursor: pointer !important;
}

#storages_table {
    height: 90%;
    max-height: 300px;
    overflow-y: hidden !important;
}
</style>

<meta name='viewport' content='width=device-width, initial-scale=1.0 maximum-scale=1.0 user-scalable=0' />

<link href='./lib/fonts/fontawesome/css/fontawesome.css' rel='stylesheet'>
<link href='./lib/fonts/fontawesome/css/solid.css' rel='stylesheet'>
<link href='./lib/fonts/fontawesome/css/brands.css' rel='stylesheet'>
<link href='./lib/js/tabulator/tabulator.min.css' rel='stylesheet'>
<script src='./lib/js/sweetalert2/sweetalert2.all.min.js'></script>
<script src='./lib/js/qrcodejs/qrcode.min.js'></script>
<script src='./lib/js/html2pdf/html2pdf.bundle.min.js'></script>
<script src='./lib/js/tabulator/tabulator.min.js'></script>

<title>Qorage - The simplest storage solution ever</title>
</head>
<body>
<div id='main_content'>

<script>

var json_cache = null;
var cookies = document.cookie;
var storage_table = null;

function init() {
    if(window.location.hash.substring(1) !== '') {
        fetch_storage(window.location.hash.substring(1));

        document.getElementById('qr_code_button').style.display = 'block';
        document.getElementById('delete_storage_button').style.display = 'block';
    }else{
        document.getElementById('qr_code_button').style.display = 'none';
        document.getElementById('delete_storage_button').style.display = 'none';
    }
}

function save_storage() {
    if(window.location.hash.substring(1) !== '') {
        set_storage({
            id: window.location.hash.substring(1),
            name: document.getElementById('storage_name').value,
            description: document.getElementById('storage_description').value,
        });
    }else{
        set_storage({
            name: document.getElementById('storage_name').value,
            description: document.getElementById('storage_description').value,
        });
    }
}

function print_qr_code() {
    Swal.fire({
        title: 'Print QR Code',
        html: '<div id=\'qr_code_container\'></div>',
        color: 'var(--main-dialog-font-color)',
        background: 'var(--main-dialog-background-color)',
        showCancelButton: true,
        confirmButtonColor: 'var(--main-dialog-button-color)',
        cancelButtonColor: 'var(--main-dialog-danger-button-color)',
        confirmButtonText: '<i class=\'fa-regular fa-print\'></i> Print',
        cancelButtonText: 'Cancel',
        width: '600px',
        didOpen: () => {
            let storage_qr_code = new QRCode('qr_code_container', {
                text: window.location.href,
                width: 128,
                height: 128,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H,
            });

            let qr_code_title = document.createElement('span');
            qr_code_title.id = 'qr_code_title';
            qr_code_title.innerText = document.getElementById('storage_name').value;
            document.getElementById('qr_code_container').appendChild(qr_code_title);
        },
    }).then((result) => {
        if(result.isConfirmed) {
            let qr_code_pdf_options = {
                margin: 0,
                filename: document.getElementById('storage_name').value + '_qr_code.pdf',
                image: {type: 'png', quality: 2},
                html2canvas: {scale: 1},
                jsPDF: { orientation: 'landscape', unit: 'mm', format: 'a4'}
            };
            
            document.getElementById('qr_code_container').style.margin = '0';

            html2pdf().set(qr_code_pdf_options).from(document.getElementById('qr_code_container')).save();
        }
    });
}

function show_storage_list() {
    Swal.fire({
        title: 'Storage List',
        html: '<div id=\'storages_table\'></div>',
        color: 'var(--main-dialog-font-color)',
        background: 'var(--main-dialog-background-color)',
        showCancelButton: true,
        showConfirmButton: false,
        cancelButtonColor: 'var(--main-dialog-danger-button-color)',
        cancelButtonText: 'Close',
        width: '600px',
        didOpen: () => {
            storage_table = new Tabulator('#storages_table', {
                height: '100%',
                placeholder: '<b>Loading storages...</b>',
                layout: 'fitDataStretch',
                selectable: false,
                columns: [
                    {field: 'open', width: '80', resizable: false, headerSort: false, formatter: 'html', hozAlign: 'left', vertAlign: 'middle'},
                    {title: 'Name', field: 'storage_name', resizable: false, formatter: 'textarea', hozAlign: 'left', vertAlign: 'middle'},
                ],
                initialSort: [
                    {column: 'storage_name', dir: 'asc'},
                ],
            });

            storage_table.on('tableBuilt', function() {
                return storages_data_result = fetch('/backend.php?get_storages=true')
                .then(response => response.text())
                .then(data => {
                    data = JSON.parse(data);
                    //console.log(data)
                    
                    if(data.status == 'ok') {
                        var storages_data = JSON.parse(data.response);
                        console.log(storages_data);
                        
                        var storages_table_data = [];
                            
                        if(json_cache != storages_data) {
    
                            storage_table.setData(storages_table_data);
                            
                            for(const [storage_id, storage] of Object.entries(storages_data)) {		
                                var open_button_string = '<div class=\'open_button_cell\' onclick=\'open_storage(this)\' storage_id=\'' + storage_id + '\' class=\'button_open_storage\'><i class=\'fa-solid fa-arrow-up-right-from-square\'></i></div>';

                                if(storage_id == window.location.hash.substring(1)) {
                                    open_button_string = '<i class=\'fa-solid fa-location-dot\'></i>';
                                }

                                storages_table_data.push({open: open_button_string, storage_name: storage.name});
                            }
                            
                            if(storages_table_data.length > 0) {
                                storage_table.setData(storages_table_data);
                            }else{
                                document.getElementById('storages_table').style = '';
                                document.getElementById('storages_table').style.textAlign = 'center';
                                document.getElementById('storages_table').style.border = '0';
                                document.getElementById('storages_table').style.fontSize = '1em';
                                document.getElementById('storages_table').innerText = 'No storages available :(';
                            }


                            json_cache = storages_data;
                        }
                    }else{
                        if(data.response != 'error_no_entries_available') {
                            Swal.fire({
                                title: 'Couldn\'t fetch storages :(',
                                color: 'var(--main-dialog-font-color)',
                                background: 'var(--main-dialog-background-color)',
                                icon: 'error',
                                confirmButtonColor: 'var(--main-dialog-button-color)',
                                width: '600px',
                                didClose: () => {
                                    location.reload();
                                },
                            });
                        }
                        
                        return false;
                    }
                });
            });
        },
    }).then((result) => {
        if(result.isConfirmed) {

        }
    });
}

function open_storage(element) {
    window.location.hash = element.getAttribute('storage_id');
}

function fetch_storage(storage_id) {
    var storage_loading_info = Swal.fire({
        title: 'Loading Storage',
        color: 'var(--main-dialog-font-color)',
        background: 'var(--main-dialog-background-color)',
        width: '600px',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });

    return storage_data_result = fetch('./backend.php?get_storage=' + storage_id)
    .then(response => response.text())
    .then(data => {
        data = JSON.parse(data);
        console.log(data)
        
        if(data.status == 'ok') {
            var storage_data = JSON.parse(data.response);
             
            if(json_cache != storage_data) {
                document.getElementById('storage_name').value = storage_data.name;
                document.getElementById('storage_description').value = storage_data.description;

                json_cache = storage_data;
            }

            storage_loading_info.close();
            return storage_data;
        }else{
            Swal.fire({
                title: 'Couldn\\'t find storage :(',
                color: 'var(--main-dialog-font-color)',
                background: 'var(--main-dialog-background-color)',
                icon: 'error',
                confirmButtonColor: 'var(--main-dialog-button-color)',
                confirmButtonText: 'Return to list',
                width: '600px',
                didClose: () => {
                    window.location = '/';
                },
            });

            storage_loading_info.close();
            return false;
        }
    });
}

function set_storage(storage_data) {
    var storage_loading_info = Swal.fire({
        title: 'Saving storage data',
        color: 'var(--main-dialog-font-color)',
        background: 'var(--main-dialog-background-color)',
        width: '600px',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });

    return storage_data_result = fetch('./backend.php?set_storage=' + JSON.stringify(storage_data))
    .then(response => response.text())
    .then(data => {
        data = JSON.parse(data);
        console.log(data)
        
        if(data.status == 'ok') {
            Swal.fire({
                title: 'Storage saved!',
                color: 'var(--main-dialog-font-color)',
                background: 'var(--main-dialog-background-color)',
                icon: 'success',
                confirmButtonColor: 'var(--main-dialog-button-color)',
                confirmButtonText: 'OK',
                width: '600px',
                didClose: () => {
                    if(data.response == 'edited_storage') {
                        init();
                    }else{
                        window.location.hash = data.response;
                    }
                },
            });

            storage_loading_info.close();
            return storage_data;
        }else{
            Swal.fire({
                title: 'Couldn\\'t save storage :(',
                color: 'var(--main-dialog-font-color)',
                background: 'var(--main-dialog-background-color)',
                icon: 'error',
                confirmButtonColor: 'var(--main-dialog-button-color)',
                confirmButtonText: 'Refresh',
                width: '600px',
                didClose: () => {
                    window.location.reload();
                },
            });

            storage_loading_info.close();
            return false;
        }
    });
}

function delete_storage(storage_id) {
    Swal.fire({
        title: 'Delete storage?',
        color: 'var(--main-dialog-font-color)',
        background: 'var(--main-dialog-background-color)',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: 'var(--main-dialog-danger-button-color)',
        cancelButtonColor: 'var(--main-dialog-button-color)',
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        width: '600px',
    }).then((result) => {
        if(result.isConfirmed) {
            var storage_loading_info = Swal.fire({
                title: 'Deleting storage',
                color: 'var(--main-dialog-font-color)',
                background: 'var(--main-dialog-background-color)',
                width: '600px',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });
        
            return storage_data_result = fetch('./backend.php?delete_storage=' + storage_id)
            .then(response => response.text())
            .then(data => {
                data = JSON.parse(data);
                console.log(data)
                
                if(data.status == 'ok') {
                    storage_loading_info.close();
        
                    Swal.fire({
                        title: 'Storage deleted!',
                        color: 'var(--main-dialog-font-color)',
                        background: 'var(--main-dialog-background-color)',
                        icon: 'success',
                        confirmButtonColor: 'var(--main-dialog-button-color)',
                        confirmButtonText: 'Refresh',
                        width: '600px',
                        didClose: () => {
                            window.location = '/';
                        },
                    });
                }else{
                    Swal.fire({
                        title: 'Couldn\\'t delete storage :(',
                        color: 'var(--main-dialog-font-color)',
                        background: 'var(--main-dialog-background-color)',
                        icon: 'error',
                        confirmButtonColor: 'var(--main-dialog-button-color)',
                        confirmButtonText: 'Refresh',
                        width: '600px',
                        didClose: () => {
                            window.location = '/';
                        },
                    });
        
                    storage_loading_info.close();
                    return false;
                }
            });
        }
    });
}

window.addEventListener('load', init);
window.addEventListener('hashchange', init);
</script>

<h1>Qorage - The simplest storage solution ever</h1>

<div id='main_form'>
    <button id='list_button' class='button' onclick='show_storage_list()' style='float: left; max-width: 90px;' title='Shows a list of all storages'><i class='fa-solid fa-list'></i>&nbsp;&nbsp;List</button>
    <button id='qr_code_button' class='button qr_code_button' onclick='print_qr_code()' style='float: right; max-width: 120px;' title='Generate printable QR code'><i class='fa-regular fa-qrcode'></i>&nbsp;&nbsp;QR Code</button>
    <br>
    <br>
    <label class='form_input_label' for='storage_name'>Storage Name:</label>
    <br>
    <input id='storage_name' class='form_input' type='text' name='storage_name' maxlength='255' required>
    <br>
    <br>
    <label class='form_input_label' for='storage_name'>Storage Description:</label>
    <br>
    <textarea id='storage_description' class='form_input' name='storage_description' maxlength='20000'></textarea>
    <br>
    <br>
    <button id='delete_storage_button' class='button_danger' onclick='delete_storage(window.location.hash.substring(1))' style='float: left;' title='Deletes the current storage'><i class='fa-regular fa-trash-can'></i>&nbsp;&nbsp;Delete Storage</button>
    <button id='save_storage_button' class='button' onclick='save_storage()' style='float: right;' title='Saves storages data'><i class='fa-regular fa-floppy-disk'></i>&nbsp;&nbsp;Save Storage</button>
</div>

</div>
</body>";

?>