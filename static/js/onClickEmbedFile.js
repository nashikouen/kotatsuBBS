function toggleEmbed(event, element) {
    event.preventDefault();  

    var linkE = event.currentTarget; 
    var thumbnailE = linkE.querySelector('img');

    var fileE = linkE.querySelector('.full');
    if (!fileE) {
        fileE = element;
        fileE.classList.add('full', 'hidden');
        linkE.appendChild(fileE);
    }

    thumbnailE.classList.toggle('hidden');
    fileE.classList.toggle('hidden');
}

documeent.addEventListener("DOMContentLoaded", function() {
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
                    console.log('This file does not have a embed format', link);
            }
        });
    });
});