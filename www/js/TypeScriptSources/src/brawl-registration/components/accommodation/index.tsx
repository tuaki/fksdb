import * as React from 'react';
import {
    Field,
} from 'redux-form';
import Row from './row';
import Price from './price';
import { connect } from 'react-redux';
import { IStore } from '../../reducers';
import { IAccommodationItem } from '../../middleware/iterfaces';

interface IProps {
    type: string;
    index: number;
}

interface IState {
    accommodationDef?: IAccommodationItem[];
}

class Accommodation extends React.Component<IProps & IState, {}> {

    public render() {
        const dates = {};
        const names = [];
        this.props.accommodationDef.forEach((value) => {
            if (names.indexOf(value.name) === -1) {
                names.push(value.name);
            }
            dates[value.date] = dates[value.date] || [];
            dates[value.date].push(value);
        });

        const rows = [];
        for (const date in dates) {
            if (dates.hasOwnProperty(date)) {
                rows.push(<Field name={date}
                                 component={Row}
                                 hotels={names}
                                 date={date}
                                 accommodations={dates[date]}/>);
            }
        }

        return <div>
            <h3>Accommodation</h3>
            <table className="table">
                <thead>
                <tr>
                    <th>Date</th>
                    {names.map((hotel) => {
                        return <th>{hotel}</th>;
                    })}
                </tr>
                </thead>
                <tbody>
                {rows}
                </tbody>
                <Price type={this.props.type} index={this.props.index}/>
            </table>
        </div>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state: IStore): IState => {
    return {
        accommodationDef: state.definitions.accommodation,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Accommodation);
