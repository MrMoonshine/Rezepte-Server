const editorID = "zubereitung";

function insertStrAt(original, str, pos){
    var ret = original.slice(0, pos);
    var backstr = original.slice(pos);
    ret += str + backstr;
    return ret;
}

// Bold, italic and code
function editorApplyAtStartAndEnd(chrs){
    var editor = document.getElementById(editorID);
    var start = editor.selectionStart;
    editor.value = insertStrAt(editor.value, chrs, editor.selectionEnd);
    editor.value = insertStrAt(editor.value, chrs, start);
}

function editorApplyHeader(num){
    var editor = document.getElementById(editorID);
    var str = "";
    for(var i = 0; i < num; i++){
        str += "#";
    }
    str += " ";

    editor.value = insertStrAt(editor.value, str, editor.selectionStart);
}