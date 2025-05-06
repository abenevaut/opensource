import { Head, useForm } from '@inertiajs/react';
import { Button } from '@abenevaut/tailwindui/src/js/CatalystInertia/Button';
import { ErrorMessage, Field, FieldGroup, Fieldset, Label, Legend } from '@abenevaut/tailwindui/src/js/CatalystInertia/fieldset'
import { Link } from '@abenevaut/tailwindui/src/js/CatalystInertia/link'
import { Input } from '@abenevaut/tailwindui/src/js/CatalystInertia/Input';
import { Text } from "@abenevaut/tailwindui/src/js/CatalystInertia/text.jsx";
import { PipelineLayout } from '@abenevaut/tailwindui/src/js/Components/pipeline-layout';
import ProcessingIcon from "@/Components/ProcessingIcon.jsx";
import Logo from "@/Components/Logo.jsx";
import SuccessAlert from "@/Components/SuccessAlert.jsx";
import { Divider } from "@abenevaut/tailwindui/src/js/CatalystInertia/divider.jsx";

export default function ForgotPassword({ status }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('password.email'));
    };

    return (
        <PipelineLayout>
            <Head title="Forgot Password"/>
            <div className="flex min-h-full flex-1 flex-col justify-center py-12 sm:px-6 lg:px-8">

                <div className="sm:mx-auto sm:w-full sm:max-w-md">
                    <Logo className="mx-auto h-10 w-auto"/>
                    <h2 className="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900 dark:text-white">
                        Forgot your password ?
                    </h2>
                </div>

                <div className="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
                    <div className="bg-white px-6 py-12 sm:rounded-lg sm:px-12 shadow dark:lg:shadow-none dark:bg-zinc-900">
                        <form onSubmit={ submit } className="space-y-6">

                            <Fieldset>

                                <Legend>Forgot your password? No problem</Legend>
                                <Text>Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.</Text>

                                <Divider className="mt-2"/>

                                <FieldGroup>

                                    {
                                        !!status
                                        && <SuccessAlert status={ status }/>
                                    }

                                </FieldGroup>

                                <FieldGroup>

                                    <Field>
                                        <Label>Email address</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            name="email"
                                            required
                                            autoComplete="email"
                                            invalid={ !!errors.email }
                                            value={ data.email }
                                            onChange={ (e) => setData('email', e.target.value) }
                                        />
                                        {
                                            !!errors.email
                                            && <ErrorMessage>{ errors.email }</ErrorMessage>
                                        }
                                    </Field>

                                    <div>
                                        <Button
                                            type='submit'
                                            disabled={ processing }
                                            className="flex w-full justify-center"
                                        >
                                            {
                                                processing
                                                    ? <ProcessingIcon processing={ processing }/>
                                                    : 'Email Password Reset Link'
                                            }
                                        </Button>
                                    </div>

                                </FieldGroup>

                            </Fieldset>

                        </form>
                    </div>

                    <p className="mt-10 text-center text-sm text-gray-500">
                        You didn't forget your password?{ ' ' }

                        <Link
                            href={ route('login') }
                            className="font-semibold leading-6 text-abenevaut-600 hover:text-abenevaut-500"
                        >
                            Connect now
                        </Link>
                    </p>
                </div>

            </div>
        </PipelineLayout>
    );
}
