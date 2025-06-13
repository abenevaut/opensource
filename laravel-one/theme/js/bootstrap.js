import PiwikPro from '@piwikpro/react-piwik-pro';
import * as Sentry from "@sentry/react";

const appEnv = import.meta.env.VITE_APP_ENV || false;
const isProductionEnvironment = 'production' === appEnv;

if (isProductionEnvironment) {
  PiwikPro.initialize('2c54d796-5f59-434c-85e2-1381de1d0d07', 'https://abenevaut.piwik.pro');
}

Sentry.init({
  dsn: 'https://ce8b13241096d9f48e86a55e950d9da6@o229053.ingest.us.sentry.io/4508267232296960',
  environment: appEnv,
});
