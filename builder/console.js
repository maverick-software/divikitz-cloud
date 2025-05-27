function handleCaptionsStream(callback, startTimestamp) {
  var playerResponse = JSON.parse(ytplayer.config.args.player_response);
  var captionsUrl = playerResponse.streamingData.adaptiveFormats.find(function (
    format
  ) {
    return format.mimeType.indexOf('text/') === 0;
  }).url;
  var domParser = new window.DOMParser();

  fetchCaptions().then(function (primaryInfo) {
    var beginningTimestamp =
      Date.now() - primaryInfo.streamProperties['Stream-Duration-Us'] / 1000;
    var startSequenceNumber = startTimestamp
      ? Math.round(
          ((startTimestamp - beginningTimestamp) * 1000) /
            primaryInfo.streamProperties['Target-Duration-Us']
        )
      : primaryInfo.streamProperties['Sequence-Number'];
    return fetchCaptionsUntilEnd(startSequenceNumber);
    function fetchCaptionsUntilEnd(sequenceNumber) {
      var timestamp =
        beginningTimestamp +
        (primaryInfo.streamProperties['Target-Duration-Us'] *
          primaryInfo.streamProperties['Sequence-Number']) /
          1000;
      return (timestamp > Date.now()
        ? waitUntil(timestamp)
        : Promise.resolve()
      ).then(function () {
        return fetchCaptions(sequenceNumber).then(function (info) {
          callback(info);
          if (info.streamProperties['Stream-Finished'] === 'F') {
            return fetchCaptionsUntilEnd(
              info.streamProperties['Sequence-Number'] + 1
            );
          }
        });
      });
    }
  });

  function fetchCaptions(sequenceNumber) {
    return fetchTextUntilContentReturned(
      captionsUrl +
        (sequenceNumber === undefined ? '' : '&sq=' + sequenceNumber)
    ).then(function (text) {
      var streamPropertiesContent = text.slice(
        text.indexOf('Sequence-Number:'),
        text.indexOf('\r\n\r\n')
      );
      var streamProperties = {};
      streamPropertiesContent.split('\n').forEach(function (line) {
        var lineParts = line.trim().split(': ');
        var key = lineParts[0];
        var value = lineParts[1];
        streamProperties[key] = isNaN(value) ? value : Number(value);
      });
      var xmlIndex = text.indexOf('<?xml ');
      var xmlContent = xmlIndex !== -1 ? text.slice(xmlIndex) : null;
      var xmlTree =
        xmlContent && domParser.parseFromString(xmlContent, 'text/xml');
      var unixTimestampRelative =
        (streamProperties['Sequence-Number'] *
          streamProperties['Target-Duration-Us']) /
        1000;
      var captions =
        xmlTree &&
        Array.prototype.map
          .call(xmlTree.querySelectorAll('p'), function (p) {
            var textContent = p.textContent;
            if (textContent.trim()) {
              var t = Number(p.getAttribute('t'));
              var d = Number(p.getAttribute('d'));
              var start = t + unixTimestampRelative;
              var end = start + d;
              return {
                text: textContent,
                start: start,
                end: end
              };
            }
          })
          .filter(Boolean);
      var webVttContent =
        captions &&
        captions
          .map(function (caption) {
            return (
              formatTime(caption.start / 1000) +
              ' --> ' +
              formatTime(caption.end / 1000) +
              '\n' +
              caption.text +
              '\n'
            );
          })
          .concat('')
          .join('\n');
      return {
        streamProperties: streamProperties,
        xmlContent: xmlContent,
        xmlTree: xmlTree,
        unixTimestampRelative: unixTimestampRelative,
        captions: captions,
        webVttContent: webVttContent
      };
    });
  }

  // for some reason we get an empty response sometimes
  function fetchTextUntilContentReturned(url) {
    return fetch(url)
      .then(function (res) {
        return res.text();
      })
      .then((text) => {
        return text || fetchTextUntilContentReturned(url);
      });
  }

  function waitUntil(unixTime) {
    return new Promise(function (resolve) {
      setTimeout(resolve, Math.max(0, unixTime - Date.now()));
    });
  }

  function pad2(number) {
    // thanks https://www.electrictoolbox.com/pad-number-two-digits-javascript/
    return (number < 10 ? '0' : '') + number;
  }

  function pad3(number) {
    return number >= 100 ? number : '0' + pad2(number);
  }

  // time: seconds
  function formatTime(time) {
    var hours = 0;
    var minutes = 0;
    var seconds = 0;
    var milliseconds = 0;
    while (time >= 60 * 60) {
      hours++;
      time -= 60 * 60;
    }
    while (time >= 60) {
      minutes++;
      time -= 60;
    }
    while (time >= 1) {
      seconds++;
      time -= 1;
    }
    milliseconds = (time * 1000).toFixed(0);
    return (
      pad2(hours) +
      ':' +
      pad2(minutes) +
      ':' +
      pad2(seconds) +
      '.' +
      pad3(milliseconds)
    );
  }
}