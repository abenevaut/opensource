import { Link, useForm } from '@inertiajs/react';
// import { Transition } from '@headlessui/react';
import { Button } from "@abenevaut/tailwindui/src/js/CatalystInertia/Button";
import { ErrorMessage, Field, FieldGroup, Label } from "@abenevaut/tailwindui/src/js/CatalystInertia/fieldset.jsx";
import { Input } from "@abenevaut/tailwindui/src/js/CatalystInertia/input.jsx";
import ProcessingIcon from "@/Components/ProcessingIcon.jsx";
import { Fieldset } from "@abenevaut/tailwindui/src/js/CatalystInertia/fieldset";

export default function UpdateProfileInformation({ user, mustVerifyEmail, status, className = '' }) {
    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm({
        email: user.email,
    });

    const submit = (e) => {
        e.preventDefault();

        patch(route('profile.update'));
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-gray-900">Profile Information</h2>

                <p className="mt-1 text-sm text-gray-600">
                    Update your account's profile information and email address.
                </p>
            </header>

            <form onSubmit={submit} className="mt-6 space-y-6">

                <Fieldset>

                    {/*<FieldGroup>*/}

                    {/*    {*/}
                    {/*        !!status*/}
                    {/*        && <SuccessAlert status={ status }/>*/}
                    {/*    }*/}

                    {/*</FieldGroup>*/}

                    <FieldGroup>

                        {/*<Field>*/}
                        {/*    <Label>Name</Label>*/}
                        {/*    <Input*/}
                        {/*        id="name"*/}
                        {/*        type="text"*/}
                        {/*        name="name"*/}
                        {/*        required*/}
                        {/*        autoComplete="name"*/}
                        {/*        invalid={ !!errors.name }*/}
                        {/*        value={ data.name }*/}
                        {/*        onChange={ (e) => setData('name', e.target.value) }*/}
                        {/*    />*/}
                        {/*    {*/}
                        {/*        !!errors.name*/}
                        {/*        && <ErrorMessage>{ errors.name }</ErrorMessage>*/}
                        {/*    }*/}
                        {/*</Field>*/}

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

                        {mustVerifyEmail && user.email_verified_at === null && (
                            <div>
                                <p className="text-sm mt-2 text-gray-800">
                                    Your email address is unverified.
                                    <Link
                                        href={route('verification.send')}
                                        method="post"
                                        as="button"
                                        className="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        Click here to re-send the verification email.
                                    </Link>
                                </p>

                                {status === 'verification-link-sent' && (
                                    <div className="mt-2 font-medium text-sm text-green-600">
                                        A new verification link has been sent to your email address.
                                    </div>
                                )}
                            </div>
                        )}

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


                {/*<div>*/}
                {/*    <InputLabel htmlFor="name" value="Name"/>*/}

                {/*    <TextInput*/}
                {/*        id="name"*/}
                {/*        className="mt-1 block w-full"*/}
                {/*        value={ data.name }*/}
                {/*        onChange={ (e) => setData('name', e.target.value) }*/}
                {/*        required*/}
                {/*        isFocused*/}
                {/*        autoComplete="name"*/}
                {/*    />*/}

                {/*    <InputError className="mt-2" message={ errors.name }/>*/}
                {/*</div>*/}

                {/*<div>*/}
                {/*    <InputLabel htmlFor="email" value="Email"/>*/}

                {/*    <TextInput*/}
                {/*        id="email"*/}
                {/*        type="email"*/}
                {/*        className="mt-1 block w-full"*/}
                {/*        value={data.email}*/}
                {/*        onChange={(e) => setData('email', e.target.value)}*/}
                {/*        required*/}
                {/*        autoComplete="username"*/}
                {/*    />*/}

                {/*    <InputError className="mt-2" message={errors.email} />*/}
                {/*</div>*/}


                {/*<div className="flex items-center gap-4">*/}
                {/*    <Button disabled={processing}>Save</Button>*/}

                {/*    <Transition*/}
                {/*        show={recentlySuccessful}*/}
                {/*        enter="transition ease-in-out"*/}
                {/*        enterFrom="opacity-0"*/}
                {/*        leave="transition ease-in-out"*/}
                {/*        leaveTo="opacity-0"*/}
                {/*    >*/}
                {/*        <p className="text-sm text-gray-600">Saved.</p>*/}
                {/*    </Transition>*/}
                {/*</div>*/}
            </form>
        </section>
    );
}
