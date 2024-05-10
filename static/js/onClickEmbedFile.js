function toggleEmbed(event, element) {
    event.preventDefault();  

    var linkE = event.currentTarget;
    var thumbnailE = linkE.querySelector('img');
    var fileE = linkE.querySelector('.full');

    if (!fileE) {
        fileE = element;
        fileE.classList.add('full', 'hidden');
        linkE.appendChild(fileE);

        if (linkE.className !== 'image') {
            // Create a close hyperlink
            const closeLink = document.createElement('a');
            closeLink.href = '#';
            closeLink.textContent = 'close';
            closeLink.onclick = function (e) {
                e.preventDefault();
                e.stopPropagation();
                fileE.remove();
                thumbnailE.classList.remove('hidden');  // Show the thumbnail again
                closeLink.parentNode.removeChild(closeLink.previousSibling); // Remove the '['
                closeLink.parentNode.removeChild(closeLink.nextSibling); // Remove the ']'
                closeLink.parentNode.removeChild(closeLink); // Remove the close link itself
            };

            // Locate the corresponding 'fileName' section related to the clicked link
            const links = document.querySelectorAll('.fileName a');
            for (let link of links) {
                if (link.href === linkE.href) {
                    const fileNameDiv = link.parentNode;
                    // why its fucking backwards? idfk but it works. i hate js and strangly chatgpt sucks with js. almost as its made bad on purpose to save bureaucracy
                    fileNameDiv.insertAdjacentHTML("afterbegin","]");
                    fileNameDiv.insertAdjacentElement("afterbegin",closeLink);
                    fileNameDiv.insertAdjacentHTML("afterbegin","[");
                    break;
                }
            }
        }
    }
    thumbnailE.classList.toggle('hidden');
    fileE.classList.toggle('hidden');
}

document.addEventListener("DOMContentLoaded", function() {
    var links = document.querySelectorAll('.file a');
    links.forEach(function(link) {
        link.addEventListener('click', function(event) {
            switch (link.className) {
                case 'image':
                    var img = new Image();
                    img.src = link.href;
                    toggleEmbed(event, img);
                    break;
                case 'video':
                    var video = document.createElement('video');
                    video.setAttribute('loading', 'lazy');
                    video.src = link.href;
                    video.controls = true;
                    video.autoplay = true;
                    toggleEmbed(event, video);
                    break;
                case 'swf':
                    var object = document.createElement('object');
                    var embed = document.createElement('embed');
                    embed.src = link.href;
                    object.appendChild(embed);
                    toggleEmbed(event, object);
                    break;
                default:
                    console.log('This file type does not have a predefined embed format.', link);
            }
        });
    });
});

/*  
<div class="files">
    <div class="fileName">
        <div>
            [<a href="locationOnServer.png" download="originalName.png">
                download
            </a>]
            <small>si.ze KB (widthxhight)</small>
            <a href="locationOnServer.png" target="_blank" rel="nofollow"> 
                originalName.png
            </a> 
        </div>
        <div>
            [<a href="locationOnServer2.png" download="originalName2.png">
                download
            </a>]
            <small>si.ze KB (widthxhight)</small>
            <a href="locationOnServer2.png" target="_blank" rel="nofollow"> 
                originalName2.png
            </a> 
        </div>
    </div>
    <div class="file">
        <a href="locationOnServer.png" class="image" target="_blank" rel="nofollow">
            <img src="thumbnailLocationOnServer.jpg" title="originalName.png">
        </a>
            
        <a href="locationOnServer2.png" class="image" target="_blank" rel="nofollow">
            <img src="thumbnailLocationOnServer.jpg" title="originalName.png">
        </a>
    </div>
</div>
*/