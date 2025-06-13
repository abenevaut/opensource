'use client'

import ReactDOM from 'react-dom/client';
import WithoutRouterProvider from "@abenevaut/tailwindui/src/js/Providers/WithoutRouterProvider.jsx";
import Documentation from "./Pages/Documentation.jsx";
import { AppNavbar, AppSidebar } from "./AppNavigation.jsx";
import './bootstrap.js';

ReactDOM.createRoot(document.getElementById('root')).render(
  <WithoutRouterProvider>
    <Documentation
      navbar={AppNavbar()}
      sidebar={AppSidebar()}
      withTestimonialAndStats={false}
      article={window.article}
    />
  </WithoutRouterProvider>
);
