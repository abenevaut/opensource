'use client'

import ReactDOM from 'react-dom/client';
import WithoutRouterProvider from "@abenevaut/tailwindui/src/js/Providers/WithoutRouterProvider.jsx";
import Article from "@abenevaut/tailwindui/src/js/Pages/Article.jsx";
import { AppNavbar, AppSidebar } from "./AppNavigation.jsx";
import './bootstrap.js';

ReactDOM.createRoot(document.getElementById('root')).render(
  <WithoutRouterProvider>
    <Article
      navbar={AppNavbar()}
      sidebar={AppSidebar()}
      withTestimonialAndStats={false}
      article={window.article}
    />
  </WithoutRouterProvider>
);
