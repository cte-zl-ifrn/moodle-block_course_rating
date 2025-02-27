// Seting stars received value
function setCheckedStar(value) {
    var stars = document.getElementsByClassName('rating-star');
    for(var i = 0; i < 5; i++) {
        stars[i].children [0].className = 'star-img'
        stars[i].children [1].className = 'star-img d-none'
    }

    for(var i = 0; i < value; i++) {
        stars[i].children [0].className = 'star-img d-none'
        stars[i].children [1].className = 'star-img'
    }
}

// Reseting stars to off after mouse leave
function setUnCheckedStar(value) {
    var stars = document.getElementsByClassName('rating-star');
    var rating_value = document.getElementById('rating').value
    for(var i = rating_value; i < value; i++) {
        stars[i].children [0].className = 'star-img'
        stars[i].children [1].className = 'star-img d-none'
    }
}
// Light up stars on mouse hover
function starOn(value) {
    var stars = document.getElementsByClassName('rating-star');
    for(var i = 0; i < value; i++) {
        stars[i].children [0].className = 'star-img d-none'
        stars[i].children [1].className = 'star-img'
    }
}

// Confirm stars checkeds after click
function setRating(value) {
    document.getElementById('rating').value = value
    setCheckedStar(value)
}

// Load users ratings using ajax
function loadRatings() {
    var offset = document.getElementById('offset_ratings').value;
    var course = document.getElementById('courseid').value;

    const xhttp = new XMLHttpRequest()
    xhttp.onload = function() {
        if(this.responseText.length == 0)
            document.getElementById('btn_load_ratings').style.display = 'none';
        else{
            var obj = JSON.parse(this.responseText);
            document.getElementById('container_ratings').innerHTML += obj.content;
            document.getElementById('btn_load_ratings').innerHTML = obj.button_show_more;
            if(obj.ratings_remaining <= 0 )
                document.getElementById('btn_load_ratings').style.display = 'none';

        }
    }
    xhttp.open('GET', '../blocks/course_rating/endpoint.php?section=COMMENTS&course='+course+'&offset='+offset);
    xhttp.send();

    document.getElementById('offset_ratings').value = parseInt(offset) + 5;
}

// Load total rating and stars using ajax
function loadRatingBars() {
    var course = document.getElementById('courseid').value;

    const xhttp = new XMLHttpRequest()
    xhttp.onload = function () {
        document.getElementById('container_ratings_bars').innerHTML = this.responseText
    }
    xhttp.open('GET', '../blocks/course_rating/endpoint.php?section=RATINGS&course='+course);
    xhttp.send();

}

function init_load_rating() {
    var plugin_container = document.getElementById('plugin_block_ratings_container');

    if(plugin_container) {
        plugin_container.innerHTML = '' +
        '<input type="hidden" name="offset_ratings" id="offset_ratings" value="0"  autocomplete="off">'+
        '<div id="container_ratings_bars"></div> '+
        '<div id="container_ratings"></div>'+
        '<div class="d-flex justify-content-center">'+
        '   <button type="button" onclick="loadRatings()" id="btn_load_ratings" class="btn btn-secondary"> Mostrar mais comentários</button>'+
        '</div>';

        // Loading all comments in course page
        if(document.getElementById('offset_ratings') && document.getElementById('courseid'))
            loadRatings();

        // Loading ratings and bars
        if(document.getElementById('container_ratings_bars'))
            loadRatingBars();

    }
}

//Mouse over stars effect
var stars = document.getElementsByClassName('rating-star');
stars.forEach(element => {
    element.addEventListener('mouseover', (event) =>{
        starOn(element.dataset.block_rating)
    })
    element.addEventListener('mouseout', (event) =>{
        setUnCheckedStar(element.dataset.block_rating)
    })
    element.addEventListener('click', (event)=> {
        setRating(element.dataset.block_rating);
    })
});

// Show edit button or edit form
if(document.getElementById('edit_rating_button')) {
    document.getElementById('edit_rating_button').addEventListener('click', (event) => {
        document.getElementById('rating_message').style.display = 'none';
        document.getElementById('rating_form').style.display = 'block';
    });
    document.getElementById('cancel_rating_button').addEventListener('click', (event) => {
        document.getElementById('rating_message').style.display = 'flex';
        document.getElementById('rating_form').style.display = 'none';
    });
} 

(function() {
   // your page initialization code here
   // the DOM will be available here,0

    // Seting stars checkeds
    if(document.getElementById('rating'))
      setCheckedStar(document.getElementById('rating').value);

    // Load all content html in single div
    if(document.getElementById('plugin_block_ratings_container')){
        init_load_rating();
    }
})();


