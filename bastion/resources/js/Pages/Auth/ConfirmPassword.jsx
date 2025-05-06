import { useEffect } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Button } from '@abenevaut/tailwindui/src/js/CatalystInertia/Button';
import { Field, FieldGroup, Fieldset, Label, Legend } from '@abenevaut/tailwindui/src/js/CatalystInertia/fieldset'
import { Input } from '@abenevaut/tailwindui/src/js/CatalystInertia/Input';
import { Text } from "@abenevaut/tailwindui/src/js/CatalystInertia/text.jsx";
import { PipelineLayout } from '@abenevaut/tailwindui/src/js/Components/pipeline-layout';
import ProcessingIcon from "@/Components/ProcessingIcon.jsx";
import Logo from "@/Components/Logo.jsx";

export default function ConfirmPassword() {
    const { data, setData, post, processing, errors, reset } = useForm({
        password: '',
    });

    useEffect(() => {
        return () => {
            reset('password');
        };
    }, []);

    const submit = (e) => {
        e.preventDefault();

        post(route('password.confirm'));
    };

    return (
        <PipelineLayout>
            <Head title="Confirm Password"/>
            <div className="flex min-h-full flex-1 flex-col justify-center py-12 sm:px-6 lg:px-8">

                <div className="sm:mx-auto sm:w-full sm:max-w-md">
                    <Logo className="mx-auto h-10 w-auto"/>
                    <h2 className="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900 dark:text-white">
                        Confirm Password
                    </h2>
                </div>

                <div className="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
                    <div className="bg-white px-6 py-12 sm:rounded-lg sm:px-12 shadow dark:lg:shadow-none dark:bg-zinc-900">
                        <form onSubmit={ submit } className="space-y-6">

                            <Fieldset>

                                <Legend>Confirm Password</Legend>
                                <Text>This is a secure area of the application. Please confirm your password before continuing.</Text>

                                <FieldGroup>

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

                                    <div>
                                        <Button
                                            type='submit'
                                            disabled={ processing }
                                            className="flex w-full justify-center"
                                        >
                                            {
                                                processing
                                                    ? <ProcessingIcon processing={ processing }/>
                                                    : 'Confirm your password'
                                            }
                                        </Button>
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
