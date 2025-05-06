import { useEffect } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Button } from '@abenevaut/tailwindui/src/js/CatalystInertia/Button';
import { ErrorMessage, Field, FieldGroup, Fieldset, Label } from '@abenevaut/tailwindui/src/js/CatalystInertia/fieldset'
import { Link } from '@abenevaut/tailwindui/src/js/CatalystInertia/link'
import { Input } from '@abenevaut/tailwindui/src/js/CatalystInertia/Input';
import { PipelineLayout } from '@abenevaut/tailwindui/src/js/Components/pipeline-layout';
import ProcessingIcon from "@/Components/ProcessingIcon.jsx";
import Logo from "@/Components/Logo.jsx";

export default function ResetPassword({ token, email }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        token: token,
        email: email,
        password: '',
        password_confirmation: '',
    });

    useEffect(() => {
        return () => {
            reset('password', 'password_confirmation');
        };
    }, []);

    const submit = (e) => {
        e.preventDefault();

        post(route('password.store'));
    };

    return (
        <PipelineLayout>
            <Head title="Reset your password"/>
            <div className="flex min-h-full flex-1 flex-col justify-center py-12 sm:px-6 lg:px-8">

                <div className="sm:mx-auto sm:w-full sm:max-w-md">
                    <Logo className="mx-auto h-10 w-auto"/>
                    <h2 className="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900 dark:text-white">
                        Reset your password
                    </h2>
                </div>

                <div className="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
                    <div className="bg-white px-6 py-12 sm:rounded-lg sm:px-12 shadow dark:lg:shadow-none dark:bg-zinc-900">
                        <form onSubmit={ submit } className="space-y-6">

                            <Fieldset>

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

                                    <Field>
                                        <Label>Password</Label>
                                        <Input
                                            id="password"
                                            type="password"
                                            name="password"
                                            required
                                            autoComplete="new-password"
                                            value={ data.password }
                                            onChange={ (e) => setData('password', e.target.value) }
                                        />
                                        {
                                            !!errors.password
                                            && <ErrorMessage>{ errors.password }</ErrorMessage>
                                        }
                                    </Field>

                                    <Field>
                                        <Label>Confirm password</Label>
                                        <Input
                                            id="password_confirmation"
                                            type="password"
                                            name="password_confirmation"
                                            required
                                            autoComplete="new-password"
                                            value={ data.password_confirmation }
                                            onChange={ (e) => setData('password_confirmation', e.target.value) }
                                        />
                                        {
                                            !!errors.password_confirmation
                                            && <ErrorMessage>{ errors.password_confirmation }</ErrorMessage>
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
                                                    : 'Reset Password'
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
