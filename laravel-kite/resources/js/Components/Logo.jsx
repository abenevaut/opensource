export default function Logo({ className }) {
    return (
        <img
            alt="logo"
            src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%2377BAA9'/%3E%3Ctext x='50' y='55' font-size='50' text-anchor='middle' fill='white' font-family='sans-serif'%3EK%3C/text%3E%3C/svg%3E"
            className={ className }
        />
    );
}