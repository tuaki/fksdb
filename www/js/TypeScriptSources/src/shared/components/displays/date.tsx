import * as React from 'react';
import { lang } from '../../../i18n/i18n';

interface IProps {
    date: string;
}

export default class DateDisplay extends React.Component<IProps, {}> {

    public render() {
        const date = new Date(this.props.date);
        return <span>{date.toLocaleDateString(lang.getBCP47())}</span>;
    }
}
