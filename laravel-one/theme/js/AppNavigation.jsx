'use client'

import { ArrowTopRightOnSquareIcon, ChevronUpIcon, ChevronDownIcon } from '@heroicons/react/16/solid';
import { Avatar } from '@abenevaut/tailwindui/src/js/Catalyst/avatar';
import { Dropdown, DropdownButton, DropdownDescription, DropdownItem, DropdownLabel, DropdownMenu } from '@abenevaut/tailwindui/src/js/Catalyst/dropdown';
import { Navbar, NavbarItem, NavbarLabel, NavbarSection, NavbarDivider, NavbarSpacer } from '@abenevaut/tailwindui/src/js/Catalyst/navbar';
import { Sidebar, SidebarBody, SidebarFooter, SidebarHeader, SidebarItem, SidebarLabel, SidebarSection, SidebarSpacer } from '@abenevaut/tailwindui/src/js/Catalyst/sidebar';
import logoUrl from '@abenevaut/maskot-2013/dist/app-icon.webp';
import './bootstrap.js';
import ThemeSwitchNavbarItem from "@abenevaut/tailwindui/src/js/Components/theme-switch-navbar-item.jsx";

function MainDropdownMenu() {
  return (
    <DropdownMenu className="min-w-80 lg:min-w-64" anchor="bottom start">

      <DropdownItem href="https://www.abenevaut.dev/index.html?pk_campaign=redirect-laravel-one-github-pages&pk_source=laravel-one.abenevaut.dev&pk_medium=showcase&pk_keyword=link&pk_content=v1&pk_cid=20241123">
        <ArrowTopRightOnSquareIcon/>
        <DropdownLabel>Portfolio</DropdownLabel>
      </DropdownItem>

      <DropdownItem href="https://github.com/abenevaut/phpunit-slicer">
        <ArrowTopRightOnSquareIcon/>
        <DropdownLabel>phpunit-slicer</DropdownLabel>
        <DropdownDescription>is a tool to slice PHPUnit tests files to tests suites</DropdownDescription>
      </DropdownItem>

    </DropdownMenu>
  );
}

export function AppNavbar() {
  return (
    <Navbar/>
  );
}

export function AppSidebar() {

    let pathname = '/'; //useLocation().pathname;
    const navItems = [
        { label: 'Home', url: '/', urlAlt: ['/index.html'] },
        { label: 'Create your content', url: 'create-your-content.html' },
        { label: 'Generate GitHub pages', url: 'generate-github-pages.html' },
    ];

    const navFooterItems = [
        { label: 'Support', url: 'https://github.com/abenevaut/opensource/issues?q=is%3Aopen+is%3Aissue+label%3Alaravel-one' },
        { label: 'Changelog', url: 'https://github.com/abenevaut/opensource/releases?q=laravel-one-&expanded=true' },
    ];

  return (
    <Sidebar>

      <SidebarHeader>
        <Dropdown>

          <DropdownButton as={ SidebarItem } className="lg:mb-2.5">
            <Avatar src={ logoUrl }/>
            <SidebarLabel>Antoine Benevaut</SidebarLabel>
            <ChevronDownIcon/>
          </DropdownButton>

          <MainDropdownMenu/>

        </Dropdown>
      </SidebarHeader>

      <SidebarBody>
        <SidebarSection>
          { navItems.map(({ label, url }) => (
            <SidebarItem key={ label } href={ url } current={pathname === url}>
              { label }
            </SidebarItem>
          )) }
        </SidebarSection>

        <SidebarSpacer />

        <SidebarSection>

            <SidebarSection>
                { navFooterItems.map(({ label, url }) => (
                    <SidebarItem key={ label } href={ url } current={pathname === url}>
                        { label }
                    </SidebarItem>
                )) }
            </SidebarSection>

            <ThemeSwitchNavbarItem menu="sidebar" />

        </SidebarSection>

      </SidebarBody>

    </Sidebar>
  );
}
