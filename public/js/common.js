
function modal_show(target = "") {
    if(target.trim() != "") {
        /**/
        target = target.trim();
        try{
            if(target.charAt(0) != "#") {
                target = "#" + target.trim();
            }
        }catch(err){}
        /**/
        var el = document.querySelector(target)
        var modal = tailwind.Modal.getOrCreateInstance(el);
        modal.show();
    }
}

function modal_hide(target = "") {
    if(target.trim() != "") {
        /**/
        target = target.trim();
        try{
            if(target.charAt(0) != "#") {
                target = "#" + target.trim();
            }
        }catch(err){}
        /**/
        var el = document.querySelector(target)
        var modal = tailwind.Modal.getOrCreateInstance(el);
        modal.hide();
        /**/
    }
}
