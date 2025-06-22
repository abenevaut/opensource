import Error from "@/Layouts/Error.jsx";

export default function Error429() {
    return (
        <Error
            title="Too Many Requests"
            message="You have made too many requests in a short period of time. Please try again later."
        />
    );
}
