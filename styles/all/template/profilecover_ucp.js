document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('ucp') || document.getElementById('user_profile');
    var isAcp = !!document.getElementById('user_profile');
    var input = document.getElementById('profile_cover');
    var fileName = document.getElementById('profilecover-file-name');
    var editorWrap = document.getElementById('profilecover-editor-wrap');
    var editor = document.getElementById('profilecover-editor');
    var posXInput = document.getElementById('profile_cover_pos_x');
    var posYInput = document.getElementById('profile_cover_pos_y');

    if (form) {
        form.enctype = 'multipart/form-data';
        form.setAttribute('enctype', 'multipart/form-data');
    }

    if (!input || !fileName || !editor || !editorWrap || !posXInput || !posYInput) {
        return;
    }

    var emptyText = fileName.getAttribute('data-empty-text') || fileName.textContent;
    var objectUrl = null;
    var isDragging = false;
    var currentX = clamp(parseInt(posXInput.value || editor.getAttribute('data-pos-x') || '50', 10));
    var currentY = clamp(parseInt(posYInput.value || editor.getAttribute('data-pos-y') || '50', 10));

    function clamp(value) {
        if (isNaN(value)) {
            return 50;
        }
        return Math.max(0, Math.min(100, value));
    }

    function applyPosition() {
        posXInput.value = currentX;
        posYInput.value = currentY;
        editor.style.backgroundPosition = currentX + '% ' + currentY + '%';
    }

    function setPositionFromPointer(event) {
        var rect = editor.getBoundingClientRect();
        if (!rect.width || !rect.height) {
            return;
        }

        var clientX = event.clientX;
        var clientY = event.clientY;

        if (typeof event.touches !== 'undefined' && event.touches.length) {
            clientX = event.touches[0].clientX;
            clientY = event.touches[0].clientY;
        }

        currentX = clamp(Math.round(((clientX - rect.left) / rect.width) * 100));
        currentY = clamp(Math.round(((clientY - rect.top) / rect.height) * 100));
        applyPosition();
    }

    applyPosition();

    input.addEventListener('change', function () {
        if (input.closest('.profilecover-upload--acp')) {
            input.classList.add('profilecover-upload__input--selected');
        }
        if (objectUrl && window.URL && typeof URL.revokeObjectURL === 'function') {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }

        if (input.files && input.files.length > 0) {
            var file = input.files[0];
            fileName.textContent = file.name;

            if (window.URL && typeof URL.createObjectURL === 'function') {
                objectUrl = URL.createObjectURL(file);
                editor.style.backgroundImage = 'url(' + objectUrl + ')';
                editorWrap.classList.remove('profilecover-editor-wrap--hidden');
                currentX = 50;
                currentY = 50;
                applyPosition();
            }
        } else {
            fileName.textContent = emptyText;
        }
    });

    editor.addEventListener('mousedown', function (event) {
        isDragging = true;
        editor.classList.add('is-dragging');
        setPositionFromPointer(event);
        event.preventDefault();
    });

    document.addEventListener('mousemove', function (event) {
        if (!isDragging) {
            return;
        }
        setPositionFromPointer(event);
    });

    document.addEventListener('mouseup', function () {
        isDragging = false;
        editor.classList.remove('is-dragging');
    });

    editor.addEventListener('touchstart', function (event) {
        isDragging = true;
        editor.classList.add('is-dragging');
        setPositionFromPointer(event);
    }, { passive: true });

    editor.addEventListener('touchmove', function (event) {
        if (!isDragging) {
            return;
        }
        setPositionFromPointer(event);
    }, { passive: true });

    document.addEventListener('touchend', function () {
        isDragging = false;
        editor.classList.remove('is-dragging');
    });

    editor.addEventListener('click', function (event) {
        if (!isDragging) {
            setPositionFromPointer(event);
        }
    });
});
