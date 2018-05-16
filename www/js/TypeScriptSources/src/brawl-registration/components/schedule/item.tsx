import * as React from 'react';
import { IPrice } from '../../middleware/price';

interface IProps {
    date: string;
    description: string;
    id: number;
    scheduleName: string;
    price?: IPrice;
    time: {
        begin: string;
        end: string;
    };
}

interface IState {
    showDescription: boolean;
}

export default class Item extends React.Component<IProps & any, IState> {
    constructor() {
        super();
        this.state = {showDescription: false};
    }

    public render() {
        const {input, input: {value, onChange}, date, description, id, scheduleName, time: {begin, end}} = this.props;
        return <div className="card">
            <div className={'card-header'}>
                <div className="row">
                    <div className="col-6 form-group form-check">
                        <h5 className={value ? 'text-success' : ''}>
                            <a onClick={(event) => {
                                event.preventDefault();
                                onChange(!value);
                            }}>{value ?
                                (<i className="fa fa-check-square-o"/>) :
                                (<i className="fa fa-square-o"/>)
                            }</a>
                            <span className="ml-3">{scheduleName}</span>
                        </h5>
                    </div>
                    <div className="col-6">
                        <button className={'ml-3 btn pull-right btn-info'} onClick={(event) => {
                            event.preventDefault();
                            this.setState({showDescription: !this.state.showDescription});
                        }}><span className="fa fa-info"/>
                        </button>
                        <small className={'pull-right text-muted'}>{date} {begin}-{end}</small>
                    </div>
                </div>
            </div>
            <div className={'card-body ' + (this.state.showDescription ? 'd-block' : 'd-none')}>
                {description}
            </div>
        </div>;

    }
}
