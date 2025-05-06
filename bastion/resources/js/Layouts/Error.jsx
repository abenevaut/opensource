import { Head } from '@inertiajs/react';
import { FieldGroup, Fieldset } from '@abenevaut/tailwindui/src/js/CatalystInertia/fieldset'
import { Link } from '@abenevaut/tailwindui/src/js/CatalystInertia/link'
import { PipelineLayout } from '@abenevaut/tailwindui/src/js/Components/pipeline-layout';
import Logo from "@/Components/Logo.jsx";
import { Legend } from "@abenevaut/tailwindui/src/js/CatalystInertia/fieldset.jsx";
import { Text } from "@abenevaut/tailwindui/src/js/CatalystInertia/text.jsx";

export default function Error({ title, message }) {
    return (
        <PipelineLayout>
            <Head title={ title }/>
            <div className="flex min-h-full flex-1 flex-col justify-center py-12 sm:px-6 lg:px-8">

                <div className="sm:mx-auto sm:w-full sm:max-w-md">
                    <Logo className="mx-auto h-10 w-auto"/>
                    <h2 className="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">
                        { title }
                    </h2>
                </div>

                <div className="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
                    <div className="bg-white px-6 py-12 shadow sm:rounded-lg sm:px-12">

                        <Fieldset>

                            <Legend>{ title }</Legend>
                            <Text>{ message }</Text>

                            <FieldGroup>

                                <div className="flex items-center justify-between">
                                    <div className="text-sm leading-6">
                                        <Link
                                            href={ route('home') }
                                            className="text-sm font-semibold leading-6 text-gray-900"
                                        >
                                            Back to home page
                                        </Link>
                                    </div>
                                </div>

                            </FieldGroup>

                        </Fieldset>
                    </div>
                </div>

            </div>
        </PipelineLayout>
    );
}
