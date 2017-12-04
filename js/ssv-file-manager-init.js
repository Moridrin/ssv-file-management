jQuery(function ($) {
    $(document).ready(function () {
        var dropzone = document.getElementById('dropzone');
        dropzone.ondrop = function(e) {
            console.log('test');
            var length = e.dataTransfer.items.length;
            for (var i = 0; i < length; i++) {
                var entry = e.dataTransfer.items[i].webkitGetAsEntry();
                if (entry.isFile) {
                    console.log('file: '+entry);
                } else if (entry.isDirectory) {
                    console.log('folder: '+entry);
                }
            }
        };
    });
});
