// resources/js/app.js
import 'bootstrap';
import $ from 'jquery';

console.log('App JS loaded');

$(document).ready(function() {
    // Example: highlight h1 on click
    $('h1').on('click', function() {
        $(this).css('color', '#007bff');
    });
});
