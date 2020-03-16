let apiUrl = `${window.location.protocol}//${window.location.hostname}/?rest_route=/myAwesomePlugin/v1/`;

$().ready(async () => {
  let wonderfullFunctions = {
    // Fill function used for filling the star when hovering
    fill: (element) => {
        let index = $(".fa-star").index(element) + 1;
        $(".fa-star").slice(0, index).addClass('hover');
    },
    // clear both checked and hovered stars
    clear: () => {
        $(".fa-star").filter('.hover').removeClass('hover');
        $(".fa-star").filter('.checked').removeClass('checked');
    },
    // Resets to default value.
    reset: () => {
        $('.fa-star').slice(0, currentValue).addClass('checked');
    },
    sendRating: async (rating) => {
      if(currentValue == rating.rating){
        return;
      }
      //add Loading rings
      $('#userRatings').append('<div id="loadingRing" class="sbl-circ-path"></div>');
      let status = await fetch(`${apiUrl}setRating/post_id=${currentPost}/rating=${currentValue}`).then(response => response.json());
      $('#loadingRing').remove();
    }
  };

  let currentPost = post_vars.postID;
  let orginalRating = await fetch(`${apiUrl}getRating/post_id=${currentPost}`).then(response => response.json());

  let currentValue = (orginalRating.ErrorCode == 5) ? 0 : orginalRating.rating;

  wonderfullFunctions.reset();

  $(".fa-star").mouseenter((event) => {
      event.preventDefault();
      wonderfullFunctions.clear();
      wonderfullFunctions.fill(event.target);
  });

  $(".fa-star").mouseleave((event) => {
      event.preventDefault();
      wonderfullFunctions.clear();
      wonderfullFunctions.reset();
  });

  $('.fa-star').click((event) => {
      event.preventDefault();
      let index = $(".fa-star").index(event.target) + 1;
      currentValue = index;
      wonderfullFunctions.reset();
      wonderfullFunctions.sendRating(orginalRating);
  });
});

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}