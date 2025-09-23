'use client'

import ReactDOM from 'react-dom/client';
import WithoutRouterProvider from "@abenevaut/tailwindui/src/js/Providers/WithoutRouterProvider.jsx";
import Terms from "@abenevaut/tailwindui/src/js/Pages/Terms.jsx";
import { AppNavbar, AppSidebar } from "./AppNavigation.jsx";
import './bootstrap.js';

ReactDOM.createRoot(document.getElementById('root')).render(
  <WithoutRouterProvider>
    <Terms
      navbar={AppNavbar()}
      sidebar={AppSidebar()}
    />
  </WithoutRouterProvider>,
);
