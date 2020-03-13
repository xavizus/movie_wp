let apiUrl = `${window.location.protocol}//${window.location.hostname}/?rest_route=/myAwesomePlugin/v1/`;

$(init);

function init() {
  // Listen for click
  $("#movie_autoFill").on('click', async () => {
    let data = $("#movie_imdb_id").val();
    // Check if variable is empty
    if (!data) {
      return;
    }
    // Fetch the movie
    let results = await fetch(`${apiUrl}movies/${data}`)
      .then(response => response.json());

    // Store IMDb-id field
    let imbd_id_block = $("#movie_imdb_id");

    // Check if the field is validated
    if (imbd_id_block.hasClass('is-invalid') || imbd_id_block.hasClass('is-valid')) {
      imbd_id_block.removeClass('is-invalid');
      imbd_id_block.removeClass('is-valid');
    }

    // if our resualt is negative
    if (results.Response == "False") {
      imbd_id_block.addClass('is-invalid');
      $("#movie_autofilled").val('0');
      return;
    } else {
      imbd_id_block.addClass('is-valid');
      $("#movie_autofilled").val('1');
    }

    // Create blocks for image and content.
    /*
    let imageblock = wp.blocks.createBlock('core/image', {
      url: results.Poster
    })
    */
    let block = wp.blocks.createBlock('core/paragraph', {
      content: results.Plot
    });

    // insert blocks to the editor
    wp.data.dispatch('core/block-editor').insertBlocks([block, imageblock]);

    // change title.
    wp.data.dispatch('core/editor').editPost({
      title: results.Title
    });

    $("#movie_released").val(results.Released);
    $("#movie_actors").val(results.Actors);
    $('#movie_poster').val(results.Poster);
  });

  $("#myAwesomePluginMovieBox").on("change", async () => {
    $("#movie_autofilled").val('0');
  });

  let reload_check = false;
  let publish_button_click = false;
  add_publish_button_click = setInterval(() => {
    $publish_button = $('.editor-post-publish-panel__header-publish-button .editor-post-publish-button');
    $save_draft_button = $('.edit-post-header__settings .editor-post-save-draft');
    if (($publish_button || $save_draft_button) && !publish_button_click) {
      console.log('True!');
      publish_button_click = true;
      $publish_button.add($save_draft_button).on('click', ()=>  {
        console.log('Clicked!');
        let reloader = setInterval(() => {
          if (reload_check) {
            return;
          } else {
            reload_check = true;
          }
          postsaving = wp.data.select('core/editor').isSavingPost();
          autosaving = wp.data.select('core/editor').isAutosavingPost();
          success = wp.data.select('core/editor').didPostSaveRequestSucceed();
          console.log('Saving: ' + postsaving + ' - Autosaving: ' + autosaving + ' - Success: ' + success);
          if (postsaving || autosaving || !success) {
            classic_reload_check = false;
            return;
          }
          clearInterval(reloader);
          window.location.href = window.location.href + '&refreshed=1';
        }, 1000);
      });
    }
  }, 500);
}