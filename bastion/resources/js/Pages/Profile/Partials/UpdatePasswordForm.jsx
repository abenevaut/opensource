import { useRef } from 'react';
import { useForm } from '@inertiajs/react';
import { Transition } from '@headlessui/react';
import { Field, FieldGroup, Label } from "@abenevaut/tailwindui/src/js/CatalystInertia/fieldset.jsx";
import SuccessAlert from "@/Components/SuccessAlert.jsx";
import { Input } from "@abenevaut/tailwindui/src/js/CatalystInertia/input.jsx";
import { Button } from "@abenevaut/tailwindui/src/js/CatalystInertia/Button";
import ProcessingIcon from "@/Components/ProcessingIcon.jsx";
import { Fieldset } from "@abenevaut/tailwindui/src/js/CatalystInertia/fieldset";

export default function UpdatePasswordForm({ className = '' }) {
    const passwordInput = useRef();
    const currentPasswordInput = useRef();

    const { data, setData, errors, put, reset, processing, recentlySuccessful } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const updatePassword = (e) => {
        e.preventDefault();

        put(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => reset(),
            onError: (errors) => {
                if (errors.password) {
                    reset('password', 'password_confirmation');
                    passwordInput.current.focus();
                }

                if (errors.current_password) {
                    reset('current_password');
                    currentPasswordInput.current.focus();
                }
            },
        });
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-gray-900">Update Password</h2>

                <p className="mt-1 text-sm text-gray-600">
                    Ensure your account is using a long, random password to stay secure.
                </p>
            </header>

            <form onSubmit={updatePassword} className="mt-6 space-y-6">

                <Fieldset>

                    <FieldGroup>

                        {
                            !!status
                            && <SuccessAlert status={ status }/>
                        }

                    </FieldGroup>

                    <FieldGroup>

                        <Field>
                            <Label>Current password</Label>
                            <Input
                                ref={ currentPasswordInput }
                                id="current_password"
                                type="password"
                                name="current_password"
                                required
                                autoComplete="current-password"
                                value={ data.current_password }
                                onChange={ (e) => setData('current_password', e.target.value) }
                            />
                        </Field>

                        <Field>
                            <Label>Password</Label>
                            <Input
                                ref={ passwordInput }
                                id="password"
                                type="password"
                                name="password"
                                required
                                autoComplete="new-password"
                                value={ data.current_password }
                                onChange={ (e) => setData('password', e.target.value) }
                            />
                        </Field>

                        <Field>
                            <Label>Password confirmation</Label>
                            <Input
                                ref={ passwordInput }
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                                autoComplete="new-password"
                                value={data.password_confirmation}
                                onChange={ (e) => setData('password_confirmation', e.target.value) }
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
                                        : 'Log in'
                                }
                            </Button>
                        </div>

                    </FieldGroup>

                </Fieldset>

                <div className="flex items-center gap-4">
                    <Button disabled={processing}>Save</Button>

                    <Transition
                        show={recentlySuccessful}
                        enter="transition ease-in-out"
                        enterFrom="opacity-0"
                        leave="transition ease-in-out"
                        leaveTo="opacity-0"
                    >
                        <p className="text-sm text-gray-600">Saved.</p>
                    </Transition>
                </div>
            </form>
        </section>
    );
}
