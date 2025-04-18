import './module/nuonic-plugin-installer';
import './component/nuonic-plugin-installer-index';
import './extension/sw-search-bar-item';
import './decorator/search-type.decorator';
import './component/nuonic-extension-card';
import './service/api/install.service';

import deDE from './snippet/de-DE';
import enGB from './snippet/en-GB';

Shopware.Locale.extend('de-DE', deDE);
Shopware.Locale.extend('en-GB', enGB);
