const Api = function() {};
const toQueryStringValue = value => {
  return encodeURIComponent(
    typeof value === "object" ? value.join("|") : value
  );
};
Api.prototype.ajax = function(params) {
  params["origin"] = "*";
  const q = Object.keys(params)
    .map(key => {
      return `${key}=${toQueryStringValue(params[key])}`;
    })
    .join("&");
  return fetch(`https://en.wikipedia.org/w/api.php?${q}`).then(r => r.json());
};

const cx = {
  SiteMapper: function() {
    this.getPageUrl = (language, title) =>
      `https://${language}.wikipedia.org/wiki/${title}`;
  }
};

const mw = {
  Api,
  util: {},
  config: {
    get: name => {
      switch (name) {
        case "wgUserName":
          return "MWProxyUser";
        case "wgUserLanguage":
          return "en";
        case "wgPageName":
          return "Special:ContentTranslation";
        default:
          return null;
      }
    }
  },
  cx
};

global.mw = mw;
