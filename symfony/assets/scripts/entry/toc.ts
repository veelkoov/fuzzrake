import * as jQuery from "jquery";
import * as tocbot from "tocbot";

import "../../styles/toc.scss";

jQuery(() => {
  tocbot.init({
    tocSelector: "#sk-toc",
    contentSelector: "#sk-content",
    headingSelector: "h1, h2, h3, h4, h5, h6",
    extraLinkClasses: "text-decoration-none",
    orderedList: false,
  });
});
