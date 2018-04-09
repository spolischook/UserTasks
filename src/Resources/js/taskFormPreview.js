'use strict';

import $ from 'jquery';

function readURL(input) {

    if (input.files && input.files[0]) {
        let reader = new FileReader();

        reader.onload = function(e) {
            $('#js-image-preview').attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);
    }
}

$(".js-img-file").change(function() {
    readURL(this);
});

const inputUsername = document.getElementById('user-task-username');
const inputEmail = document.getElementById('user-task-email');
const inputText = document.getElementById('user-task-text');

let updateUsername = function(){
    document.getElementById('user-task-username-preview').innerHTML = inputUsername.value;
};
let updateEmail = function(){
    document.getElementById('user-task-email-preview').innerHTML = inputEmail.value;
};
let updateText = function(){
    document.getElementById('user-task-text-preview').innerHTML = inputText.value;
};

// Because username can be prefilled
$(document).ready(updateUsername);
$(document).ready(updateEmail);
$(document).ready(updateText);
inputUsername.onkeyup = updateUsername;
inputEmail.onkeyup = updateEmail;
inputText.onkeyup = updateText;
