function __dropdown_close(id = ""){

    const myDropdown = tailwind.Dropdown.getOrCreateInstance(document.querySelector(id));
    myDropdown.hide();

}

function __dropdown_toggle(id){

    const myDropdown = tailwind.Dropdown.getOrCreateInstance(document.querySelector(id));
    myDropdown.show();

}

