import { useEffect } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Checkbox, CheckboxField } from '@abenevaut/tailwindui/src/js/CatalystInertia/Checkbox';
import { Button } from '@abenevaut/tailwindui/src/js/CatalystInertia/Button';
import { ErrorMessage, Field, FieldGroup, Fieldset, Label } from '@abenevaut/tailwindui/src/js/CatalystInertia/fieldset'
import { Link } from '@abenevaut/tailwindui/src/js/CatalystInertia/link'
import { Input } from '@abenevaut/tailwindui/src/js/CatalystInertia/Input';
import { PipelineLayout } from '@abenevaut/tailwindui/src/js/Components/pipeline-layout';
import ProcessingIcon from "@/Components/ProcessingIcon.jsx";
import Logo from "@/Components/Logo.jsx";
import SuccessAlert from "@/Components/SuccessAlert.jsx";

export default function Login({ status, canResetPassword, canRegister }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    useEffect(() => {
        return () => {
            reset('password');
        };
    }, []);

    const submit = (e) => {
        e.preventDefault();

        post(route('login'));
    };

    return (
        <PipelineLayout>
            <Head title="Sign in to your account"/>
            <div className="flex min-h-full flex-1 flex-col justify-center py-12 sm:px-6 lg:px-8">

                <div className="sm:mx-auto sm:w-full sm:max-w-md">
                    <Logo className="mx-auto h-10 w-auto"/>
                    <h2 className="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900 dark:text-white">
                        Sign in to your account
                    </h2>
                </div>

                <div className="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
                    <div className="bg-white px-6 py-12 sm:rounded-lg sm:px-12 shadow dark:lg:shadow-none dark:bg-zinc-900">
                        <form onSubmit={ submit } className="space-y-6">

                            <Fieldset>

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

                                    <Field>
                                        <Label>Password</Label>
                                        <Input
                                            id="password"
                                            type="password"
                                            name="password"
                                            required
                                            autoComplete="current-password"
                                            value={ data.password }
                                            onChange={ (e) => setData('password', e.target.value) }
                                        />
                                    </Field>

                                    <div className="flex items-center justify-between">
                                        <CheckboxField>
                                            <Checkbox
                                                name="remember"
                                                checked={ data.remember }
                                                onChange={ (checked) => setData('remember', checked) }
                                            />
                                            <Label>Remember me</Label>
                                        </CheckboxField>

                                        { canResetPassword && (
                                            <div className="text-sm leading-6">
                                                <Link
                                                    href={ route('password.request') }
                                                    className="text-sm font-semibold leading-6 text-gray-900 dark:text-white"
                                                >
                                                    Forgot your password?
                                                </Link>
                                            </div>
                                        ) }
                                    </div>

                                    <div>
                                        <Button
                                            type='submit'
                                            disabled={ processing }
                                            className="flex w-full justify-center"
                                        >
                                            {
                                                processing
                                                    ? <ProcessingIcon processing={ processing }/>
                                                    : 'Log in'
                                            }
                                        </Button>
                                    </div>

                                </FieldGroup>

                            </Fieldset>

                        </form>
                    </div>

                    { canRegister && (
                        <p className="mt-10 text-center text-sm text-gray-500">
                            Not a member?{ ' ' }

                            <Link
                                href={ route('register') }
                                className="font-semibold leading-6 text-abenevaut-500 hover:text-abenevaut-600 dark:text-abenevaut-600 dark:hover:text-abenevaut-500"
                            >
                                Register now
                            </Link>
                        </p>
                    ) }
                </div>

            </div>
        </PipelineLayout>
    );
}
