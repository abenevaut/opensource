'use client'

import ReactDOM from 'react-dom/client';
import WithoutRouterProvider from "@abenevaut/tailwindui/src/js/Providers/WithoutRouterProvider.jsx";
import HomeBlog from "@abenevaut/tailwindui/src/js/Pages/HomeBlog.jsx";
import { AppNavbar, AppSidebar } from "./AppNavigation.jsx";
import './bootstrap.js';

ReactDOM.createRoot(document.getElementById('root')).render(
  <WithoutRouterProvider>
    <HomeBlog
      navbar={AppNavbar()}
      sidebar={AppSidebar()}
      projects={window.projects}
    />
  </WithoutRouterProvider>
);
