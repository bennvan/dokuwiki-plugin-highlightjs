/*
 * Highlight code blocks and place copy button
 * @author Ben van Magill <ben.vanmagill16@gmail.com>
 */

function initHighlightJS(event){

        // select all code blocks
        document.querySelectorAll('pre.code, pre.file, pre.hljs').forEach(elmt => {
            // Highlight element
            hljs.highlightElement(elmt);
            var btn = document.createElement("DIV");
            btn.title = 'Copy to Clipboard';
            btn.classList.add('codeclipboard_button');
            var parent = elmt.parentElement;
            var wrapper;
            if (parent.classList.contains('code-toolbar')) {
                wrapper = true;
            } else {
                wrapper = false;
            }
            // If parent wrapper is present, append to that. 
            if (wrapper) {
                parent.appendChild(btn);
            } else {
                elmt.appendChild(btn);
            }
            
            // Add the click function
            btn.onclick = function() {
                if (wrapper){
                    parent.classList.add('copied');
                    setTimeout(function(){
                        parent.classList.remove('copied');
                    }, 1000);
                } else {
                    elmt.classList.add('copied');
                    setTimeout(function(){
                        elmt.classList.remove('copied');
                    }, 1000);
                }
                var txt = document.createElement('TEXTAREA'),
                // Remove any $ meta tags that shouldnt be copied in shell commands
                    child = elmt.firstChild,
                    texts = [],
                    is_shell = elmt.classList.contains('language-shell');

                if (is_shell) {
                    while (child) {
                        if (child.nodeType == 3) {
                            texts.push(child.data);
                        } 
                        else if (!child.classList.contains("hljs-meta")) {
                            texts.push(child.innerText);
                        }
                        child = child.nextSibling;
                    }
                    txt.innerHTML = texts.join("");

                } else {
                    txt.innerHTML = elmt.innerText;
                }

                elmt.appendChild(txt);
                txt.select();
                document.execCommand('copy');
                elmt.removeChild(txt);
            }

        });

    };
    
    if(window.addEventListener) {
        window.addEventListener("load",initHighlightJS,false);
    } else if(window.attachEvent) {
        window.attachEvent("onload",initHighlightJS);
    }
