'use client'

import SidebarApp from '@abenevaut/tailwindui/src/js/Layouts/SidebarApp.jsx';
import ContentSectionWithTestimonialAndStats from "@abenevaut/tailwindui/src/js/Components/content-section-with-testimonial-and-stats.jsx";
import ScrollToHash from "@abenevaut/tailwindui/src/js/Components/scroll-to-hash.jsx";
import Markdown from "@abenevaut/tailwindui/src/js/Components/markdown.jsx";
import { Navbar } from "@abenevaut/tailwindui/src/js/Catalyst/navbar.jsx";
import { Sidebar } from "@abenevaut/tailwindui/src/js/Catalyst/sidebar.jsx";

export default function Article({ article, meta = {}, withTestimonialAndStats = false, navbar = <Navbar/>, sidebar = <Sidebar/> }) {
    return (
        <SidebarApp
            navbar={navbar}
            sidebar={sidebar}
        >
            <ScrollToHash />
            <div className="container">

                {
                    withTestimonialAndStats
                        ? (
                            <ContentSectionWithTestimonialAndStats
                                contentCategory={ meta.contentCategory }
                                contentTitle={ meta.contentTitle }
                                contentBody={ meta.contentBody }
                                caption={ meta.caption }
                                captionImage={ meta.captionImage }
                                captionAuthor={ meta.captionAuthor ?? '' }
                                captionAuthorTitle={ meta.captionAuthorTitle ?? '' }
                                stats={ meta.stats }
                                outlink={ meta.outlink }
                                outlinkTitle={ meta.outlinkTitle }
                            />
                        )
                        : ''
                }

                <div className="mx-auto lg:w-3/4 space-y-4">
                    <Markdown article={ article }/>
                </div>

            </div>
        </SidebarApp>
    );
}
