'use client'

// import { useLocation } from "react-router-dom";
import { StackedLayout } from '@abenevaut/tailwindui/src/js/Catalyst/stacked-layout';
import { Avatar } from '@abenevaut/tailwindui/src/js/CatalystInertia/avatar';
import { Dropdown, DropdownButton, DropdownItem, DropdownLabel, DropdownMenu, DropdownDivider } from '@abenevaut/tailwindui/src/js/CatalystInertia/dropdown';
import { Navbar, NavbarItem, NavbarLabel, NavbarSection, NavbarDivider, NavbarSpacer } from '@abenevaut/tailwindui/src/js/CatalystInertia/navbar';
import { Sidebar, SidebarBody, SidebarHeader, SidebarItem, SidebarLabel, SidebarSection } from '@abenevaut/tailwindui/src/js/CatalystInertia/sidebar';
import { ArrowRightStartOnRectangleIcon, ChevronDownIcon } from '@heroicons/react/16/solid';
import logoUrl from '@abenevaut/maskot-2013/dist/app-icon.webp';

const navItems = [
  { label: 'Dashboard', url: route('home') },
  { label: 'My profile', url: route('profile.edit') },
];

function TeamDropdownMenu() {
  return (
    <DropdownMenu className="min-w-80 lg:min-w-64" anchor="bottom start">

      <DropdownItem href="/">
        <DropdownLabel>Other App</DropdownLabel>
      </DropdownItem>

    </DropdownMenu>
  )
}

export default function App({ user, children }) {

  const userInitials = user?.email?.charAt(0) ?? 'AB';

  let pathname = '/'; //useLocation().pathname;

  return (
    <StackedLayout
      className="pb-10 pt-2"
      navbar={
        <Navbar>

          <Dropdown>

            <DropdownButton as={ NavbarItem } className="max-lg:hidden">
              <Avatar src={logoUrl}/>
              <NavbarLabel>User configuration</NavbarLabel>
              <ChevronDownIcon/>
            </DropdownButton>

            <TeamDropdownMenu/>

          </Dropdown>

          <NavbarDivider className="max-lg:hidden"/>

          <NavbarSection className="max-lg:hidden">

            { navItems.map(({ label, url }) => (
              <NavbarItem key={ label } href={ url } current={pathname === url}>
                { label }
              </NavbarItem>
            )) }

          </NavbarSection>

          <NavbarSpacer/>

          <NavbarSection>

            <Dropdown>
              <DropdownButton as={ NavbarItem }>
                <Avatar initials={userInitials} square/>
              </DropdownButton>

              <DropdownMenu className="min-w-64" anchor="bottom end">

                <DropdownItem href="/terms.html">
                  <DropdownLabel>Terms of services</DropdownLabel>
                </DropdownItem>

                <DropdownItem href="/privacy.html">
                  <DropdownLabel>Privacy policy</DropdownLabel>
                </DropdownItem>

                <DropdownDivider/>

                <DropdownItem href={route('logout')} method="post" as="button">
                  <ArrowRightStartOnRectangleIcon/>
                  <DropdownLabel>Sign out</DropdownLabel>
                </DropdownItem>

              </DropdownMenu>

            </Dropdown>
          </NavbarSection>
        </Navbar>
      }
      sidebar={
        <Sidebar>
          <SidebarHeader>
            <Dropdown>
              <DropdownButton as={ SidebarItem } className="lg:mb-2.5">
                <Avatar src={ logoUrl }/>
                <SidebarLabel>Tailwind Labs</SidebarLabel>
                <ChevronDownIcon/>
              </DropdownButton>
              <TeamDropdownMenu/>
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
          </SidebarBody>
        </Sidebar>
      }
    >
      {children}
    </StackedLayout>
  )
}
