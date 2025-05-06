import { Link } from '@abenevaut/tailwindui/src/js/CatalystInertia/link'
import { Text } from "@abenevaut/tailwindui/src/js/CatalystInertia/text.jsx";
import { PipelineLayout } from '@abenevaut/tailwindui/src/js/Components/pipeline-layout';
import ProcessingIcon from "@/Components/ProcessingIcon.jsx";
import Logo from "@/Components/Logo.jsx";
import SuccessAlert from "@/Components/SuccessAlert.jsx";
import { Head, useForm } from "@inertiajs/react";
import { FieldGroup, Fieldset, Legend } from "@abenevaut/tailwindui/src/js/CatalystInertia/fieldset";
import { Button } from "@abenevaut/tailwindui/src/js/CatalystInertia/Button";

export default function VerifyEmail({ status }) {
    const { post, processing } = useForm({});

    const submit = (e) => {
        e.preventDefault();

        post(route('verification.send'));
    };

    return (
        <PipelineLayout>
            <Head title="Email Verification"/>
            <div className="flex min-h-full flex-1 flex-col justify-center py-12 sm:px-6 lg:px-8">

                <div className="sm:mx-auto sm:w-full sm:max-w-md">
                    <Logo className="mx-auto h-10 w-auto"/>
                    <h2 className="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900 dark:text-white">
                        Email Verification
                    </h2>
                </div>

                <div className="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
                    <div className="bg-white px-6 py-12 sm:rounded-lg sm:px-12 shadow dark:lg:shadow-none dark:bg-zinc-900">
                        <form onSubmit={ submit } className="space-y-6">

                            <Fieldset>

                                <Legend>Email Verification</Legend>
                                <Text>Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.</Text>

                                <FieldGroup>

                                    {
                                        status === 'verification-link-sent'
                                        && <SuccessAlert status="A new verification link has been sent to the email address you provided during registration."/>
                                    }

                                </FieldGroup>

                                <FieldGroup>

                                    <div>
                                        <Button
                                            type='submit'
                                            disabled={ processing }
                                            className="flex w-full justify-center"
                                        >
                                            {
                                                processing
                                                    ? <ProcessingIcon processing={ processing }/>
                                                    : 'Resend Verification Email'
                                            }
                                        </Button>

                                        <Link
                                            href={ route('logout') }
                                            method="post"
                                            as="button"
                                            className="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-abenevaut-500"
                                        >
                                            Log Out
                                        </Link>

                                    </div>

                                </FieldGroup>

                            </Fieldset>

                        </form>
                    </div>

                </div>

            </div>
        </PipelineLayout>
    );
}
