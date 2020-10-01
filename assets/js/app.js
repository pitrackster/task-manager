/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
const $ = require('jquery');
global.$ = global.jQuery = $;
$.fn.dataTable = $.fn.DataTable = global.DataTable = require('datatables.net-bs4');

require('bootstrap');

require('bootstrap/js/dist/tooltip');
require('bootstrap/js/dist/popover');

require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');


import '../css/app.scss';



$(() => {
    $('[data-toggle="popover"]').popover();
    $('[data-toggle="tooltip"]').tooltip();
    $('.data-table').DataTable();


    $('.card-togglable').on('click', e => {
        // can not use 'find' because it will fire an error...
        const $body = $(e.target).closest('.card').children('.card-body');
        $($body).toggle();
    });

});

