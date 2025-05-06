import logoUrl from '@abenevaut/maskot-2013/dist/app-icon.webp';

export default function Logo({ className }) {
    return (
        <img
            alt="avatar"
            src={ logoUrl }
            className={ className }
        />
    );
}
