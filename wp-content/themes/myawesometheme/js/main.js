async function fetchMultipleUrls(urls) {

  let tasks = urls.map(async url => {
    let response = await fetch(url);
    let data = await response.json();

    return data;
  });

  let metaData = await Promise.all(tasks);

  return metaData;
}

let apiUrl = `${window.location.protocol}//${window.location.hostname}/?rest_route=/ourAwesomePlugin/v1/`;

let metakeys = [
  "noofrooms",
  "kvm",
  "initialbid"
];


$(async function () {
  let urls = [];

  for (let metakey of metakeys) {
    urls.push(`${apiUrl}metakeyMinMax/${metakey}`);
  }

  let data = await fetchMultipleUrls(urls);

  data.map(metadata => {
    $(`#${metadata.metakey}-range`).slider({
      range: true,
      min: Number(metadata.min),
      max: Number(metadata.max),
      values: [Number(metadata.min), Number(metadata.max)],
      slide: function (event, ui) {
        $(`#${metadata.metakey}`).val(ui.values[0] + ` ${metadata.metalabel} - ` + ui.values[1] + ` ${metadata.metalabel}`);
        $(`#min${metadata.metakey}`).val(ui.values[0]);
        $(`#max${metadata.metakey}`).val(ui.values[1]);
      }
    });
    $(`#${metadata.metakey}`).val($(`#${metadata.metakey}-range`).slider("values", 0) +
      ` ${metadata.metalabel} - ` + $(`#${metadata.metakey}-range`).slider("values", 1) + ` ${metadata.metalabel}`);
      $(`#min${metadata.metakey}`).val($(`#${metadata.metakey}-range`).slider("values", 0));
      $(`#max${metadata.metakey}`).val($(`#${metadata.metakey}-range`).slider("values", 1));
  });

});



$(function () {
  let url = apiUrl + "autocomplete";
  $("#search-properties").autocomplete({
    source: url,
    delay: 300,
    minLength: 3
  });
});